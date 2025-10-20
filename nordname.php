<?php
/**
 * NordName Domain Name Registrar Module
 * @copyright Copyright (c) C-Soft Oy dba NordName 2025
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/ApiClient.php';
require_once __DIR__ . '/resources/domains/additionalfields.php';

use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Domain\TopLevel\ImportItem;
use WHMCS\Carbon;
use WHMCS\Domain\Registrar\Domain;
use WHMCS\Module\Registrar\NordName\ApiClient as ApiClient;
use WHMCS\Database\Capsule;
use WHMCS\Exception\Module\InvalidConfiguration;

/**
 * Define module related metadata
 *
 * Provide some module information including the display name and API Version to
 * determine the method of decoding the input values.
 *
 * @return array
 */
function nordname_MetaData() {
    return array(
        'DisplayName' => 'NordName',
        'APIVersion' => '3.0',
    );
}

/**
 * Define registrar configuration options.
 *
 * The values you return here define what configuration options
 * we store for the module. These values are made available to
 * each module function.
 *
 * You can store an unlimited number of configuration settings.
 * The following field types are supported:
 *  * Text
 *  * Password
 *  * Yes/No Checkboxes
 *  * Dropdown Menus
 *  * Radio Buttons
 *  * Text Areas
 *
 * @return array
 */
function nordname_getConfigArray() {
    return array(
        // Friendly display name for the module
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'NordName',
        ),
        // a password field type allows for masked text input
        'api_key' => array(
            'Type' => 'text',
            'FriendlyName' => 'API Key',
            'Size' => '128',
            'Default' => '',
            'Description' => 'Enter your secret API key here.',
        ),
        'auxiliary_contact' => array(
            'Type' => 'text',
            'FriendlyName' => 'Admin/tech/billing contact',
            'Size' => '128',
            'Default' => '',
            'Description' => 'Enter the contact which is to be used as the admin/tech/billing contact on new orders.',
        ),
        'auto_renew' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Auto Renew',
            'Description' => 'Do you want new domain registrations to automatically renew at NordName?',
        ),
        // the yesno field type displays a single checkbox option
        'sandbox' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Sandbox Mode',
            'Description' => 'Tick to enable',
        ),
        'display_restrictions' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Display restriction information',
            'Description' => 'Experimental: Should any applicable TLD restrictions be shown to the customer on order page?',
        ),
        'price_sync_one_year' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Price sync: First year only',
            'Description' => 'Should the price sync tool set prices only for 1 year orders? It is useful if you do not want to provide longer periods'
        ),
        'price_sync_use_discounts' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Price sync: Use discounted prices?',
            'Description' => 'Should the price sync tool take into account discount campaigns when setting TLD prices? Note that the Data Sync tool does not automatically update the prices when campaigns end.'
        ),
        'redact_additional_fields' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Redaction: Redact additional fields?',
            'Description' => 'Check this option to redact values of additional fields from WHMCS. It may be useful from data protection perspective to not store all additional fields in WHMCS, such as Social Security Numbers. This is only applied to only active domains as part of the Domain Sync cron job.',
        ),
        'additional_fields_not_to_redact' => array(
            'Type' => 'text',
            'Size' => '128',
            'FriendlyName' => 'Redaction: List of additional fields to NOT redact',
            'Description' => 'Comma-separated list of additional fields to not redact. E.g. "registrant_type,vat_number". If empty, all additional fields will be redacted.',
            'Default' => 'registrant_type',
        ),
        'override_registrar' => array(
            'Type' => 'yesno',
            'FriendlyName' => 'Override all new orders to NordName',
            'Description' => 'Check this if you would like all new domain orders to be activated with NordName by default. This will override "Registrar" field when order is accepted.',
        ),
    );
}

function nordname_config_validate($params) {
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;
    $api = new ApiClient($apiKey, $sandbox);

    // Validate API key by querying TLD list.
    try {
        $api->call("GET", "tld", array("type" => "TLDList"));
    } catch (\Exception $e) {
        throw new InvalidConfiguration('Invalid API key.');
    }
  
    // Validate contact IDs
    try {
        $registrant = $api->call("GET", "contact/" . $params["auxiliary_contact"], array());
        if (!empty($registrant["is_registrant"]) && $registrant["is_registrant"] != 0) {
            throw new InvalidConfiguration('Auxiliary Admin/Tech/Billing contact is not a registrant.');
        }
    } catch (\Exception $e) {
        throw new InvalidConfiguration('Auxiliary Admin/Tech/Billing contact is invalid.');
    }
}

/**
 * Register a domain.
 *
 * Attempt to register a domain with the domain registrar.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_RegisterDomain($params) {
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;
    $auto_renew = ($params['auto_renew'] == "on") ? 'true' : 'false';

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
    $years = $params['regperiod'];

    /**
     * Nameservers.
     *
     * If purchased with web hosting, values will be taken from the
     * assigned web hosting server. Otherwise uses the values specified
     * during the order process.
     */
    $nameservers = array($params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']);

    // registrant information
    $firstName = $params["firstname"];
    $lastName = $params["lastname"];
    $fullName = $params["fullname"]; // First name and last name combined
    $company = $params["companyname"];
    $email = $params["email"];
    $address1 = $params["address1"];
    $address2 = $params["address2"];
    $city = $params["city"];
    $state = $params["state"]; // eg. TX
    $stateFullName = $params["fullstate"]; // eg. Texas
    $postcode = $params["postcode"]; // Postcode/Zip code
    $countryCode = $params["countrycode"]; // eg. GB
    $countryName = $params["countryname"]; // eg. United Kingdom
    $phoneNumber = $params["phonenumber"]; // Phone number as the user provided it
    $phoneCountryCode = $params["phonecc"]; // Country code determined based on country
    $phoneNumberFormatted = $params["fullphonenumber"]; // Format: +CC.xxxxxxxxxxxx
    
    $body = array(
        'first_name' => $firstName,
        'last_name' => $lastName,
        'address1' => $address1,
        'city' => $city,
        'area' => $state,
        'zip_code' => $postcode,
        'country' => $countryCode,
        'email' => $email,
        'phone' => $phoneNumberFormatted,
        "is_registrant" => true,
        "language" => "en",
        "additional" => new ArrayObject()
    );
    
    // Add optional fields if they are present.
    if (!empty($company))
      $body["company"] = $company;
  
    if (!empty($address2))
      $body["address2"] = $address2;
      
    // Add extra fields if required by TLD and if present.
    $tld_fields = nordname_get_tld_data($tld, $params)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];
            
        // Then same to conditional fields.
        $required_fields = $fields["required_fields"];
        foreach ($required_fields as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field])) {
                $field_metadata = nordname_additional_fields_bank($field);
                if ($field_metadata["IsStandard"]) {
                    $body[$field] = $params["additionalfields"][$field];
                } else {
                    $body["additional"][$field] = array("domain" => $sld . '.' . $tld, "value" => $params["additionalfields"][$field]);
                }
            }
        }
    }
    
    try {
        $api = new ApiClient($apiKey, $sandbox);
        // Create the contact.
        $reply = $api->call("POST", "contact", array(), $body);
        $registrant = $reply["contact"];
    
        // Build post data
        $getfields = array(
            'years' => $years,
            'auto_renew' => $auto_renew,
            'nameservers' => implode(",", array_filter($nameservers)),
            'registrant' => $registrant,
            'admin' => $params["auxiliary_contact"],
            'tech' => $params["auxiliary_contact"],
            'billing' => $params["auxiliary_contact"]
        );
        
        try {
            $reply = $api->call("POST", "domain/register/" . $sld . '.' . $tld, $getfields);
        } catch (\Exception $e) {
            // If registration failed, remove the registrant contact in order to keep contact list clean.
            $api->call("DELETE", "contact/" . $registrant, array());
            throw $e;
        }

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Initiate domain transfer.
 *
 * Attempt to create a domain transfer request for a given domain.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain transfer order
 * * When a pending domain transfer order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_TransferDomain($params) {
    $apiKey = $params['api_key'];
    $auto_renew = ($params['auto_renew'] == "on") ? 'true' : 'false';
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
    $epp = $params['eppcode'];

    // registrant information
    $firstName = $params["firstname"];
    $lastName = $params["lastname"];
    $fullName = $params["fullname"]; // First name and last name combined
    $company = $params["companyname"];
    $email = $params["email"];
    $address1 = $params["address1"];
    $address2 = $params["address2"];
    $city = $params["city"];
    $state = $params["state"]; // eg. TX
    $stateFullName = $params["fullstate"]; // eg. Texas
    $postcode = $params["postcode"]; // Postcode/Zip code
    $countryCode = $params["countrycode"]; // eg. GB
    $countryName = $params["countryname"]; // eg. United Kingdom
    $phoneNumber = $params["phonenumber"]; // Phone number as the user provided it
    $phoneCountryCode = $params["phonecc"]; // Country code determined based on country
    $phoneNumberFormatted = $params["fullphonenumber"]; // Format: +CC.xxxxxxxxxxxx
    
    $body = array(
        'first_name' => $firstName,
        'last_name' => $lastName,
        'address1' => $address1,
        'city' => $city,
        'area' => $stateFullName,
        'zip_code' => $postcode,
        'country' => $countryCode,
        'email' => $email,
        'phone' => $phoneNumberFormatted,
        "is_registrant" => true,
        "language" => "en"
    );
    
    // Add optional fields if they are present.
    if (!empty($company))
      $body["company"] = $company;
  
    if (!empty($address2))
      $body["address2"] = $address2;
      
    // Add extra fields if required by TLD and if present.
    $tld_fields = nordname_get_tld_data($tld, $params)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];
            
        // Then same to conditional fields.
        $required_fields = $fields["required_fields"];
        foreach ($required_fields as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field])) {
                $field_metadata = nordname_additional_fields_bank($field);
                if ($field_metadata["IsStandard"]) {
                    $body[$field] = $params["additionalfields"][$field];
                } else {
                    $body["additional"][$field] = array("domain" => $sld . '.' . $tld, "value" => $params["additionalfields"][$field]);
                }
            }
        }
    }

    try {
        $api = new ApiClient($apiKey, $sandbox);
        // Create the contact.
        $reply = $api->call("POST", "contact", array(), $body);
        
        $registrant = $reply["contact"];
        // Build post data
        $getfields = array(
            'auth_code' => $epp,
            'auto_renew' => $auto_renew,
            'registrant' => $registrant,
            'admin' => $params["auxiliary_contact"],
            'tech' => $params["auxiliary_contact"],
            'billing' => $params["auxiliary_contact"]
        );
        
        try {
            $reply = $api->call("POST", "domain/transfer/" . $sld . '.' . $tld, $getfields);
        } catch (\Exception $e) {
            // If transfer failed, remove the registrant contact in order to keep contact list clean.
            $api->call("DELETE", "contact/" . $registrant, array());
            throw $e;
        }
        
        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_RenewDomain($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
    $years = $params['regperiod'];

    $domainFqdn = $sld . '.' . $tld;

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $api->call("POST", "domain/" . $domainFqdn . "/renew", array('years' => (int) $years));

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function normalise_domain_status($status) {
    switch ($status) {
        case "Inactive":
            return WHMCS\Domain\Registrar\Domain::STATUS_SUSPENDED;
        case "Pending Activation":
            return WHMCS\Domain\Registrar\Domain::STATUS_INACTIVE;
        case "Expired (grace period)":
        case "Expired (restore period)":
            return WHMCS\Domain\Registrar\Domain::STATUS_EXPIRED;
        case "Expired (pending delete)":
            return WHMCS\Domain\Registrar\Domain::STATUS_PENDING_DELETE;

    }
    return WHMCS\Domain\Registrar\Domain::STATUS_ACTIVE;
}

/**
 * Fetch current domain status
 *
 * This function should set the current domain status, settings and nameservers.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_GetDomainInformation($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        
        $nameServers = array();
        $x = 1;
        foreach ($reply["nameservers"] as $ns) {
            $nameServers["ns". $x] = $ns;
            $x++;
        }

        $domain = new Domain();
        $domain->setDomain($params["domainname"]);
        $domain->setNameservers($nameServers);
        $domain->setIdProtectionStatus($reply["settings"]["privacy"]);
        $domain->setRegistrationStatus(normalise_domain_status($reply["status"]));
        $domain->setTransferLock($reply["settings"]["transfer_lock"]);
        $domain->setExpiryDate(Carbon::parse($reply["expires_at"]));

        if ($reply["verification"]["registrant"]["required"]) { 
            $domain->setIsIrtpEnabled(true);
            $domain->setDomainContactChangePending(!$reply["verification"]["registrant"]["verified"]);
            $domain->setPendingSuspension(true);
            $domain->setDomainContactChangeExpiryDate(Carbon::parse($reply["verification"]["registrant"]["deadline"]));

            $tld_data = nordname_get_tld_data($tld, $params);
            if ($tld_data["technical"]["transfer_lock_after_trade"] > 0) {
                $domain->setIrtpVerificationTriggerFields(
                    [
                        'Registrant' => [
                            'First Name',
                            'Last Name',
                            'Organization Name',
                            'Email Address',
                        ],
                    ]
                );
            }
        }

        // Check status of IRTP Transfer Lock
        $irtp_transfer_lock = false;
        if ($reply["settings"]["transfer_lock_until"]) {
            $transferlock_until = Carbon::parse($reply["settings"]["transfer_lock_until"]);
            if ($transferlock_until->isFuture()) {
                $irtp_transfer_lock = true;
                $domain->setIrtpTransferLockExpiryDate($transferlock_until);
            }
        }
        $domain->setIrtpTransferLock($irtp_transfer_lock);
        
        return $domain;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function nordname_ResendIRTPVerificationEmail(array $params) {
	// user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/verification/registrant/resend", array());
        return array(
            "success" => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Fetch current nameservers. Deprecated - replaced by GetDomainInformation()
 * for WHMCS 7.6 and later
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_GetNameservers($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        return array(
            'ns1' => $reply["nameservers"][0],
            'ns2' => $reply["nameservers"][1],
            'ns3' => $reply["nameservers"][2],
            'ns4' => $reply["nameservers"][3],
            'ns5' => $reply["nameservers"][4],
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request to the
 * domain registrar.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_SaveNameservers($params) {

    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
  
    // submitted nameserver values
    $nameservers = array($params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']);

    // Build post data
    $getfields = array(
        'nameservers' => implode(",", array_filter($nameservers))
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/change_nameservers", $getfields);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get the current WHOIS Contact Information.
 *
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */

function nordname_GetContactDetails($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $domain = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        $registrant = $api->call("GET", "contact/" . $domain["registrant"], $getfields);
        return array(
            'Registrant' => array(
                'Address 1' => $registrant["address1"],
                'Address 2' => $registrant["address2"],
                'City' => $registrant["city"],
                'State' => $registrant["area"],
                'Postcode' => $registrant["zip_code"],
                'Country' => $registrant["country"],
                'Phone Number' => $registrant["phone"],
                'Email Address' => $registrant["email"],
                'Language' => $registrant["language"],
            )
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function nordname_ClientAreaCustomButtonArray() {
    return array(
        \Lang::trans('tabChangeOwner') => "trade",
    );
}

function nordname_trade($params) {

    $domainid = $params['domainid'];
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Check if domain trade is free of charge
    $trade_allowed = true;
    try {
        $tld_data = nordname_get_tld_data($tld, $params);
        if (isset($tld_data['prices']['trade']['price'])) {
            $trade_cost = $tld_data['prices']['trade']['price'];
            $trade_allowed = ($trade_cost == 0);
        }
    } catch (Exception $e) {
        // If we can't get TLD data, assume trade is not allowed
        $trade_allowed = false;
    }

    // Handle form submission
    if (isset($_POST['submit_trade']) && $trade_allowed) {
        return nordname_process_trade($params);
    }

    // Get current registrant information
    $current_registrant = null;
    $active_trades = array();
    try {
        $apiKey = $params['api_key'];
        $sandbox = ($params['sandbox'] == "on") ? true : false;
        $api = new ApiClient($apiKey, $sandbox);
        
        // Get domain information to find current registrant ID
        $domain_info = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        if (isset($domain_info['registrant'])) {
            // Get registrant contact details
            $current_registrant = $api->call("GET", "contact/" . $domain_info['registrant'], array());
        }
        
        // Get domain trades and filter for active ones (excluding Completed and Rejected)
        $trades_response = $api->call("GET", "domain-trade", array('domain' => $sld . '.' . $tld, 'status' => 'Pending,Pending at Registry,Waiting for Old Registrant,Waiting for New Registrant,Waiting for Transfer Key,Pending Documents'));
        if (isset($trades_response['data']) && !empty($trades_response['data'])) {
            foreach ($trades_response['data'] as $trade) {
                if ($trade['status'] !== 'Completed' && $trade['status'] !== 'Rejected') {
                    $active_trades[] = $trade;
                }
            }
        }
    } catch (Exception $e) {
        // If we can't get current registrant info or trades, continue without them
        $current_registrant = null;
        $active_trades = array();
    }

    return array(
        'templatefile' => 'tradedomain',
        'breadcrumb' => array(
            'clientarea.php?action=domaindetails&domainid='.$domainid.'&modop=custom&a=trade' => 'Trade Domain',
        ),
        'vars' => array(
            'sld' => $sld,
            'tld' => $tld,
            'domainid' => $domainid,
            'additional_fields' => nordname_get_additional_fields_for_tld($params),
            'current_registrant' => $current_registrant,
            'active_trades' => $active_trades,
            'trade_allowed' => $trade_allowed,
        ),
    );
}

function nordname_process_trade($params) {
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Validate required fields
    $required_fields = array('first_name', 'last_name', 'address1', 'city', 'zip_code', 'country', 'email', 'phone', 'language');
    $missing_fields = array();
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        return array(
            'templatefile' => 'tradedomain',
            'vars' => array(
                'sld' => $params['sld'],
                'tld' => $tld,
                'domainid' => $params['domainid'],
                'additional_fields' => nordname_get_additional_fields_for_tld($params),
                'error' => \Lang::trans('nordname_trade_validation_error') . ': ' . implode(', ', $missing_fields),
                'form_data' => $_POST,
            ),
        );
    }

    // Construct phone number with country calling code
    $phone = str_replace(' ', '', $_POST["phone"]);
    $countryCallingCode = $_POST["country-calling-code-phone"];
    $phoneNumberFormatted = "+{$countryCallingCode}.{$phone}";

    // Build contact data
    $contact_data = array(
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'address1' => $_POST['address1'],
        'city' => $_POST['city'],
        'zip_code' => $_POST['zip_code'],
        'country' => $_POST['country'],
        'email' => $_POST['email'],
        'phone' => $phoneNumberFormatted,
        'language' => $_POST['language'],
        'registrant_type' => $_POST['registrant_type'],
        'is_registrant' => true,
    );

    // Add optional fields
    if (!empty($_POST['company'])) {
        $contact_data['company'] = $_POST['company'];
    }
    if (!empty($_POST['address2'])) {
        $contact_data['address2'] = $_POST['address2'];
    }
    if (!empty($_POST['area'])) {
        $contact_data['area'] = $_POST['area'];
    }

    // Add additional fields based on TLD requirements
    try {
        $additional_fields_result = nordname_AdditionalDomainFields($params);
        if (isset($additional_fields_result['fields'])) {
            foreach ($additional_fields_result['fields'] as $field) {
                $field_name = $field['Name'];
                if (!empty($_POST[$field_name])) {
                    $contact_data[$field_name] = $_POST[$field_name];
                }
            }
        }
    } catch (Exception $e) {
        // Continue without additional fields if there's an error
    }

    try {
        $api = new ApiClient($apiKey, $sandbox);
        
        // Validate contact data
        $contact_response = $api->call("POST", "contact", array("validate_for_tld" => $tld, "validate_for_type" => "registrant"), $contact_data);
        
        if (!isset($contact_response['contact'])) {
            throw new Exception('Failed to create contact: ' . json_encode($contact_response));
        }
        // If successful, create it for real.
        $contact_response = $api->call("POST", "contact", array(), $contact_data);
        
        $new_registrant_id = $contact_response['contact'];
        
        // Initiate domain trade
        $trade_data = array(
            'new_registrant' => $new_registrant_id
        );
        
        $trade_response = $api->call("POST", "domain/" . $sld . '.' . $tld . "/trade", $trade_data);
        
        // Determine the trade status and appropriate message
        $trade_status = isset($trade_response['status']) ? $trade_response['status'] : 'Pending';
        $is_completed = ($trade_status === 'Completed');
        
        if ($is_completed) {
            $success_message = \Lang::trans('nordname_trade_completed');
        } else {
            $success_message = \Lang::trans('nordname_trade_processing');
        }
        
        return array(
            'templatefile' => 'tradedomain_success',
            'vars' => array(
                'sld' => $params['sld'],
                'tld' => $tld,
                'domainid' => $params['domainid'],
                'additional_fields' => nordname_get_additional_fields_for_tld($params),
                'success_message' => $success_message,
                'trade_status' => $trade_status,
                'is_completed' => $is_completed,
                'trade_response' => $trade_response,
            ),
        );

    } catch (Exception $e) {
        return array(
            'templatefile' => 'tradedomain',
            'vars' => array(
                'sld' => $params['sld'],
                'tld' => $tld,
                'domainid' => $params['domainid'],
                'additional_fields' => nordname_get_additional_fields_for_tld($params),
                'error' => 'Failed to submit domain trade request: ' . $e->getMessage(),
                'form_data' => $_POST,
            ),
        );
    }
}

/**
 * Update the WHOIS Contact Information for a given domain.
 *
 * Called when a change of WHOIS Information is requested within WHMCS.
 * Receives an array matching the format provided via the `GetContactDetails`
 * method with the values from the users input.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_SaveContactDetails($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // whois information
    $contactDetails = $params['contactdetails'];
    
    // registrant information
    $body = array(
        'first_name' => "N/A", // First and last name need to be provided, but the only way to change them is by doing a trade.
        'last_name' => "N/A",
        'address1' => $contactDetails['Registrant']['Address 1'],
        'city' => $contactDetails['Registrant']['City'],
        'area' => $contactDetails['Registrant']['State'],
        'zip_code' => $contactDetails['Registrant']['Postcode'],
        'country' => $contactDetails['Registrant']['Country'],
        'email' => $contactDetails['Registrant']['Email Address'],
        'phone' => $contactDetails['Registrant']['Phone Number'],
        'is_registrant' => true,
        'language' => "en"
    );
    
    // Add optional fields if they are present.
    if (!empty($contactDetails["Registrant"]["Company Name"]))
      $body["company"] = $contactDetails["Registrant"]["Company Name"];
  
    if (!empty($contactDetails["Registrant"]["Address 2"]))
      $body["address2"] = $contactDetails["Registrant"]["Address 2"];
      
    // Add extra fields if required by TLD and if present.
    $tld_fields = nordname_get_tld_data($tld, $params)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];

        $required_fields = $fields["required_fields"];
        foreach ($required_fields as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field]))
                $body[$field] = $params["additionalfields"][$field];
        }
    }

    try {
        $api = new ApiClient($apiKey, $sandbox);
        
        $domain = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        $reply = $api->call("POST", "contact/" . $domain["registrant"], array(), $body);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Check Domain Availability.
 *
 * Determine if a domain or group of domains are available for
 * registration or transfer.
 *
 * @param array $params common module parameters
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 *
 * @throws Exception Upon domain availability check failure.
 *
 * @return \WHMCS\Domains\DomainLookup\ResultsList An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 */
function nordname_CheckAvailability($params) {
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // availability check parameters
    $sld = $params['searchTerm'];
    $punyCodeSLD = $params['punyCodeSearchTerm'];
    $tldsToInclude = $params['tldsToInclude'];
    $isIdnDomain = (bool) $params['isIdnDomain'];
    $premiumEnabled = (bool) $params['premiumEnabled'];
    
    if ($isIdnDomain) // If domain is an IDN domain, use the punycode format instead.
      $sld = $punyCodeSLD;
  
    $searchTerms = array();
    foreach ($tldsToInclude as $tld) {
        $searchTerms[] = $sld . $tld;
    }
    $searchTerm = implode(',', $searchTerms);
    
    // Build get data
    $getfields = array(
        'domain' => $searchTerm,
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain/availability", $getfields);
        $results = new ResultsList();
        
        foreach ($reply as $result) {
            $domainName = $result['domain'];
            $domainArr = explode('.', $domainName, 2);
            if (count($domainArr) !== 2) {
                continue;
            }
            $searchResult = new SearchResult($domainArr[0], $domainArr[1]);
            $status = SearchResult::STATUS_REGISTERED;
            if (!$result['is_premium']) {
                $status = $result['avail'] ? SearchResult::STATUS_NOT_REGISTERED : SearchResult::STATUS_REGISTERED;
            }
            $searchResult->setStatus($status);
            $results->append($searchResult);
        }

        return $results;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Get registrar lock status. Deprecated - replaced by GetDomainInformation()
 * for WHMCS 7.6 and later
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return string|array Lock status or error message
 */
function nordname_GetRegistrarLock($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Build post data
    $getfields = array();

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields);

        if (!empty($reply['settings']['transfer_lock'])) {
            return 'locked';
        } else {
            return 'unlocked';
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


/**
 * Set registrar lock status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_SaveRegistrarLock($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
  
    // lock status
    $lockStatus = $params['lockenabled'];

    // Build get data
    $getfields = array(
        'transfer_lock' => ($lockStatus == 'locked') ? 'true' : 'false'
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/feature/transfer_lock", $getfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Enable/Disable ID Protection.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_IDProtectToggle($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];
  
    // privacy status
    $privacy = ((bool) $params['protectenable']) ? 'true' : 'false';

    // Build post data
    $getfields = array(
        'privacy' => $privacy,
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/feature/privacy", $getfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Request EEP Code.
 *
 * Supports both displaying the EPP Code directly to a user or indicating
 * that the EPP Code will be emailed to the registrant.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 *
 */
function nordname_GetEPPCode($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'mode' => 'send_to_registrant',
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/retrieve_auth_code", $getfields);
      
        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Sync Domain Status & Expiration Date.
 *
 * Domain syncing is intended to ensure domain status and expiry date
 * changes made directly at the domain registrar are synced to WHMCS.
 * It is called periodically for a domain.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_Sync($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // registration parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
        $expires = new \DateTime($reply["expires_at"]);
      
        $transferredout = false;
        $active = false;
        if ($reply["status"] == "Active" || $reply["status"] == "Pending Outbound Transfer")
          $active = true;
        else if ($reply["status"] == "Transferred Out")
          $transferredout = true;

        if ($active || $transferredout) {
            nordname_redact_additional_fields($params);
        }
      
        $expired = false;
        if ($expires < new \DateTime()) // If expiration date is in the past, domain has expired.
          $expired = true;
      
        return array(
            'expirydate' => $expires->format('Y-m-d'), // Format: YYYY-MM-DD
            'active' => $active, // Return true if the domain is active
            'expired' => $expired, // Return true if the domain has expired
            'transferredAway' => $transferredout, // Return true if the domain is transferred out
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Incoming Domain Transfer Sync.
 *
 * Check status of incoming domain transfers and notify end-user upon
 * completion. This function is called daily for incoming domains.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
function nordname_TransferSync($params) {
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // domain parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'domain' => $sld . '.' . $tld,
        'type' => 'Inbound',
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        $reply = $api->call("GET", "domain-transfer", $getfields);
        $status = null;
        if (!empty($reply['data']) && count($reply['data']) > 0) {
            $status = $reply['data'][0]['status'];
        }
        $return = array();
        // If transfer has completed, we'll mark it as completed and set the expiration date.
        if ($status == "Completed") {
            $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, array());
            $return["completed"] = true;
            $return["expirydate"] = Carbon::parse($reply["expires_at"]);
        } else if ($status == "Transfer Rejected" ||
                   $status == "Domain is Locked" ||
                   $status == "Incorrect Authorization Code" ||
                   $status == "On Hold" ||
                   $status == "Domain has a pendingTransfer status") {
            $return["failed"] = true;
            $return["reason"] = $status;
        }
        return $return;
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

// Certain transfers may complete immediately.
// We shall check the status immediately after transfer request.
function nordname_ImmediateTransferCheck($params) {
    global $CONFIG;
    
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    // domain parameters
    $sld = idn_to_ascii($params["original"]["sld"]);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'domain' => $sld . '.' . $tld,
        'type' => 'Inbound',
    );

    try {
        $api = new ApiClient($apiKey, $sandbox);
        // Check if the transfer completed immediately.
        $reply = $api->call("GET", "domain-transfer", $getfields);
        $status = null;
        if (!empty($reply['data']) && count($reply['data']) > 0) {
            $status = $reply['data'][0]['status'];
        }
        if ($status == "Completed") { // If transfer has completed, we'll mark it as completed and set the expiration date to be in 1 year. Sync script will update correct expiration date.
            // Get expiration date of domain and update.
            $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields);
            $date = Carbon::parse($reply["expires_at"])->format('Y-m-d');

            $updateqrt = array();
            $updateqrt["expirydate"] = $date;
            $updateqrt["status"] = "Active";
            if ($CONFIG["DomainSyncNextDueDate"]) {
                $newexpirydate = $updateqrt["expirydate"];
                if ($CONFIG["DomainSyncNextDueDateDays"]) {
                    $newexpirydate = explode("-", $newexpirydate);
                    $newexpirydate = date("Y-m-d", mktime(0, 0, 0, $newexpirydate[1], $newexpirydate[2] - $CONFIG["DomainSyncNextDueDateDays"], $newexpirydate[0]));
                }
                $updateqrt["nextinvoicedate"] = $newexpirydate;
                $updateqrt["nextduedate"] = $updateqrt["nextinvoicedate"];
            }

            update_query('tbldomains', $updateqrt, ['id' => $params['domainid']]);
            
            sendMessage('Domain Transfer Completed', $params['domainid']);

            /** @noinspection UnusedFunctionResultInspection */
            run_hook('DomainTransferCompleted', [
                'domainId' => $params['domainid'],
                'domain' => $params['domainname'],
                'registrationPeriod' => $params['regperiod'],
                'expiryDate' => $date,
                'registrar' => 'nordname'
            ]);
        }
        
        return array('success' => true);
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Redact additional fields
 *
 * Sometimes we may not want to show the additional fields of a domain name registration
 * on the client area after a domain has been registered. Due to privacy reasons.
 *
 * @param domainid
 *
 * @return null
 */
function nordname_redact_additional_fields($params) {
    $fields_to_not_redact = explode(",", $params["additional_fields_not_to_redact"]);
    if ($params['redact_additional_fields'] == "on") {
        Capsule::table('tbldomainsadditionalfields')
                ->where('domainid', $params["domainid"])
                ->whereNotIn('name', $fields_to_not_redact)
                ->update(['value' => "REDACTED"]);
    }
}

function nordname_get_module_settings() {
    $reg = new WHMCS\Module\Registrar;
    $reg->load('nordname');
    return $reg->getSettings();
}

function nordname_get_registrar_for_tld($tld) {
    $autoreg = Capsule::table('tbldomainpricing')
            ->where('extension',  "." . $tld)
            ->value('autoreg');
    return $autoreg;
}

function nordname_get_tld_data($tld, $params=null) {
    if (empty($params)) {
        $params = nordname_get_module_settings();
    }
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;
    
    $transient_key = "nordname_" . ucfirst($tld);
    // Check if we have TLD data in cache.
    $data = WHMCS\TransientData::getInstance()->retrieve($transient_key);
    if ($data) {
        $data = json_decode($data, true);
    } else {
        $api = new ApiClient($apiKey, $sandbox);
        $data = $api->call("GET", "tld/" . $tld, array());
        WHMCS\TransientData::getInstance()->store($transient_key, json_encode($data), 3600); // Save data in cache for 1 hour.
    }
    
    return $data;
}

function nordname_get_additional_fields_for_tld($params) {
    $tld = $params["tld"];
    $fields = array();
    $added_fields = array(); // Track which fields have already been added to prevent duplicates
    $conditions_added = array();
        
    // Get TLD data from NordName API.
    $data = nordname_get_tld_data($tld, $params);
    foreach ($data["additional_contact_fields"] as $r) {
        // Check if field_name1 or field_name2 is registrant_type. In such case, append registrant_type.
        if (($r["field_name1"] == "registrant_type" || $r["field_name2"] == "registrant_type") && !in_array("registrant_type", $added_fields)) {
            $added_fields[] = "registrant_type";
            $fields[] = nordname_additional_fields_bank("registrant_type", $tld);
        }
        
        $required_fields = $r["required_fields"];
        foreach ($required_fields as $required_field) {
            // Skip if this field has already been added
            if (in_array($required_field, $added_fields)) {
                continue;
            }
            
            $obj = nordname_additional_fields_bank($required_field, $tld);
            if (!is_null($obj)) {
                // Update $obj with appropriate conditions, where appropriate.
                if ($r["field_name1"] == "registrant_type") {
                    if ($r["operator1"] == "==") {
                        $obj["Required"] = array(
                            'registrant_type' => [
                                $r["field_value1"]
                            ]
                        );
                    } else if ($r["operator1"] == "!=") {
                        $complementary = array_map(function ($x) { return explode('|', $x)[0]; }, nordname_registrant_types());
                        if (($key = array_search($r["field_value1"], $complementary)) !== false) {
                            unset($complementary[$key]);
                        }
                            
                        $obj["Required"] = array(
                            'registrant_type' => $complementary
                        );
                    }
                } else if ($r["field_name2"] == "registrant_type") {
                    if ($r["operator2"] == "==") {
                        $obj["Required"] = array(
                            'registrant_type' => [
                                $r["field_value2"]
                            ]
                        );
                    } else if ($r["operator2"] == "!=") {
                        $complementary = array_map(function ($x) { return explode('|', $x)[0]; }, nordname_registrant_types());
                        if (($key = array_search($r["field_value2"], $complementary)) !== false) {
                            unset($complementary[$key]);
                        }
                            
                        $obj["Required"] = array(
                            'registrant_type' => $complementary
                        );
                    }
                }
                
                // Mark this field as added to prevent duplicates
                $added_fields[] = $required_field;
                $fields[] = $obj;
            }
        }
    }
    return $fields;
}

function nordname_AdditionalDomainFields(array $params) {
    try {
        $fields = nordname_get_additional_fields_for_tld($params);
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
    return array("fields" => $fields);
}

function nordname_GetTldPricing(array $params) {
    // Perform API call to retrieve extension information
    // A connection error should return a simple array with error key and message
    // return ['error' => 'This error occurred',];
    
    // user defined configuration values
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;

    try {
        $api = new ApiClient($apiKey, $sandbox);
        // First get the list of TLDs supported by NordName.
        $reply = $api->call("GET", "tld", array());
        // Reply should contain an array of TLDs.
        
        $results = new ResultsList;

        require_once __DIR__ . '/price_overrides.php';
        
        foreach ($reply as $tld) {
            // Get each TLD info.
            $tld = $api->call("GET", "tld/" . $tld, array());
            
            // If admin has set the setting to only set 1 year prices, override years array with minimum value of years.
            $registration_years = $tld["technical"]["registration_years"];
            if ($params["price_sync_one_year"] == "on") {
                $registration_years = array(min($tld["technical"]["registration_years"]));
            }

            // If admin has set price overrides, override price data.
            if (isset($TLD_IMPORT_PRICE_OVERRIDES[$tld["tld"]])) {
                $tld["prices"] = $TLD_IMPORT_PRICE_OVERRIDES[$tld["tld"]];
            }
            
            // Get reg, transfer and renew standard prices.
            $reg_price = $tld["prices"]["registration"]["price"] * min($registration_years);
            $trn_price = $tld["prices"]["transfer"]["price"];
            $rnw_price = $tld["prices"]["renewal"]["price"] * min($registration_years);
            
            // Override with standard rates if they exist and feature is disabled.
            if ($params["price_sync_use_discounts"] != "on") {
                if (!empty($tld["prices"]["registration"]["standard_price"]))
                    $reg_price = $tld["prices"]["registration"]["standard_price"] * min($registration_years);
                if (!empty($tld["prices"]["transfer"]["standard_price"]))
                    $trn_price = $tld["prices"]["transfer"]["standard_price"];
                if (!empty($tld["prices"]["renewal"]["standard_price"]))
                    $rnw_price = $tld["prices"]["renewal"]["standard_price"] * min($registration_years);
            }
            
            // Form an ImportItem from the TLD information.
            $item = (new ImportItem)
                ->setExtension($tld['tld'])
                ->setYears($registration_years)
                ->setRegisterPrice($reg_price)
                ->setRenewPrice($rnw_price)
                ->setTransferPrice($trn_price)
                ->setGraceFeeDays($tld["technical"]["grace_period"])
                ->setGraceFeePrice($tld["prices"]["renewal"]["price"])
                ->setRedemptionFeeDays($tld["technical"]["redemption_period"])
                ->setRedemptionFeePrice($tld["prices"]["redemption"]["price"])
                ->setCurrency("EUR")
                ->setEppRequired($tld["features"]["supports_epp"]);
            $results[] = $item;
        }
        return $results;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

