<?php
/**
 * NordName Domain Name Registrar Module
 * @copyright Copyright (c) C-Soft Ltd 2020
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
        'APIVersion' => '1.2',
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
        )
    );
}

function nordname_config_validate($params) {
    $apiKey = $params['api_key'];
    $sandbox = ($params['sandbox'] == "on") ? true : false;
    $api = new ApiClient($apiKey);
  
    // Validate contact IDs
    try {
        $registrant = $api->call("GET", "contact/" . $params["auxiliary_contact"], array('api_key' => $apiKey), "", $sandbox);
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
        'state' => $stateFullName,
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
    $tld_fields = nordname_get_tld_data($params, $tld, $apiKey)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];
            
        // Then same to conditional fields.
        $split = explode(",", $fields["required_fields"]);
        foreach ($split as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field]))
                $body[$field] = $params["additionalfields"][$field];
        }
    }
    
    try {
        $api = new ApiClient($apiKey);
        // Create the contact.
        $reply = $api->call("POST", "contact", array('api_key' => $apiKey), $body, $sandbox);
        $registrant = $reply["contact"];
    
        // Build post data
        $getfields = array(
            'api_key' => $apiKey,
            'years' => $years,
            'auto_renew' => $auto_renew,
            'nameservers' => implode(",", array_filter($nameservers)),
            'registrant' => $registrant,
            'admin' => $params["auxiliary_contact"],
            'tech' => $params["auxiliary_contact"],
            'billing' => $params["auxiliary_contact"]
        );
        
        try {
            $reply = $api->call("POST", "domain/register/" . $sld . '.' . $tld, $getfields, "", $sandbox);
        } catch (\Exception $e) {
            // If registration failed, remove the registrant contact in order to keep contact list clean.
            $api->call("DELETE", "contact/" . $registrant, array('api_key' => $apiKey), "", $sandbox);
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
        'state' => $stateFullName,
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
    $tld_fields = nordname_get_tld_data($params, $tld, $apiKey)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];
            
        $split = explode(",", $fields["required_fields"]);
        foreach ($split as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field]))
                $body[$field] = $params["additionalfields"][$field];
        }
    }

    try {
        $api = new ApiClient($apiKey);
        // Create the contact.
        $reply = $api->call("POST", "contact", array('api_key' => $apiKey), $body, $sandbox);
        
        $registrant = $reply["contact"];
        // Build post data
        $getfields = array(
            'api_key' => $apiKey,
            'authcode' => $epp,
            'auto_renew' => $auto_renew,
            'registrant' => $registrant,
            'admin' => $params["auxiliary_contact"],
            'tech' => $params["auxiliary_contact"],
            'billing' => $params["auxiliary_contact"]
        );
        
        try {
            $reply = $api->call("POST", "domain/transfer/" . $sld . '.' . $tld, $getfields,$body, $sandbox);
        } catch (\Exception $e) {
            // If transfer failed, remove the registrant contact in order to keep contact list clean.
            $api->call("DELETE", "contact/" . $registrant, array('api_key' => $apiKey), "", $sandbox);
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

    // Build post data.
    $getfields = array(
        'api_key' => $apiKey,
        'years' => $years
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("PATCH", "domain/" . $sld . '.' . $tld . "/renew", $getfields,"", $sandbox);

        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function determine_domain_status($data) {
    if ($data["epp_status"]["clientHold"]["status"])
        return WHMCS\Domain\Registrar\Domain::STATUS_SUSPENDED;

    $status = $data["status"];
    switch ($status) {
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
    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call_v3("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        
        $nameServers = array();
        $x = 1;
        foreach ($reply["nameservers"] as $ns) {
            $nameServers["ns". $x] = $ns;
            $x++;
        }

        logModuleCall(
            'NordName',
            "get_domain",
            $reply["expires_at"],
            determine_domain_status($reply),
            "",
            array()
        );
        
        return (new Domain)
            ->setDomain($params["domainname"])
            ->setNameservers($reply["nameservers"])
            ->setIdProtectionStatus($reply["settings"]["privacy"])
            ->setRegistrationStatus(determine_domain_status($reply))
            ->setTransferLock($reply["settings"]["transfer_lock"])
            ->setExpiryDate(Carbon::parse($reply["expires_at"]))
            ->setIsIrtpEnabled(true)
            ->setIrtpOptOutStatus(false)
            ->setIrtpTransferLock(true)
            ->setIrtpTransferLockExpiryDate(Carbon::createFromFormat('Y-m-d', '2019-06-15'))
            ->setDomainContactChangePending(true)
            ->setPendingSuspension(true)
            ->setDomainContactChangeExpiryDate(Carbon::createFromFormat('Y-m-d', '2018-08-20'))
            ->setIrtpVerificationTriggerFields(
                [
                    'Registrant' => [
                        'First Name',
                        'Last Name',
                        'Organization Name',
                        'Email',
                    ],
                ]
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
    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
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
        'api_key' => $apiKey,
        'nameservers' => implode(",", array_filter($nameservers))
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/changeNameservers", $getfields,"", $sandbox);

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

    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        $domain = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        $registrant = $api->call("GET", "contact/" . $domain["registrant"], $getfields,"", $sandbox);
        return array(
            'Registrant' => array(
                'Email Address' => $registrant["email"],
                'Address 1' => $registrant["address1"],
                'Address 2' => $registrant["address2"],
                'City' => $registrant["city"],
                'State' => $registrant["state"],
                'Postcode' => $registrant["zip_code"],
                'Country' => $registrant["country"],
                'Phone Number' => $registrant["phone"]
            )
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
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
        'state' => $contactDetails['Registrant']['State'],
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
    $tld_fields = nordname_get_tld_data($params, $tld, $apiKey)["additional_contact_fields"];
    foreach ($tld_fields as $fields) {
        // First check if condition fields are present and add them.
        if (array_key_exists($fields["field_name1"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name1"]]))
            $body[$fields["field_name1"]] = $params["additionalfields"][$fields["field_name1"]];
        if (array_key_exists($fields["field_name2"], $params["additionalfields"]) && !empty($params["additionalfields"][$fields["field_name2"]]))
            $body[$fields["field_name2"]] = $params["additionalfields"][$fields["field_name2"]];

        $split = explode(",", $fields["required_fields"]);
        foreach ($split as $field) {
            if (array_key_exists($field, $params["additionalfields"]) && !empty($params["additionalfields"][$field]))
                $body[$field] = $params["additionalfields"][$field];
        }
    }

    try {
        $api = new ApiClient($apiKey);
        // Build post data
        $getfields = array(
            'api_key' => $apiKey
        );
        
        $domain = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        $reply = $api->call("POST", "contact/" . $domain["registrant"], $getfields, $body, $sandbox);

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
  
    $searchTerm = "";
    foreach ($tldsToInclude as $tld) {
        $searchTerm .= $sld . $tld . ",";
    }
    $searchTerm = substr($searchTerm, 0, -1);
    
    // Build get data
    $getfields = array(
        'api_key' => $apiKey,
        'domain' => $searchTerm,
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("GET", "domain/checkRegistrationAvailability", $getfields, "", $sandbox);
        $results = new ResultsList();
        
        foreach ($reply as $domain => $result) {
            // Instantiate a new domain search result object
            $domainArr = explode(".", $domain, 2);
            $searchResult = new SearchResult($domainArr[0], $domainArr[1]);
            // Determine the appropriate status to return
            if ($result["is_premium"]) { // NordName does support premium domains with the module yet
                $status = SearchResult::STATUS_REGISTERED;
            } else {
                if ($result["avail"]) {
                    $status = SearchResult::STATUS_NOT_REGISTERED;
                } else {
                    $status = SearchResult::STATUS_REGISTERED;
                }
            }
            $searchResult->setStatus($status);
            // Append to the search results list
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
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);

        if ($reply["transferlock"] == "true") {
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
        'api_key' => $apiKey,
        'transferlock' => ($lockStatus == 'locked') ? 'true' : 'false'
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/changeTransferLock", $getfields,"", $sandbox);

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
        'api_key' => $apiKey,
        'privacy' => $privacy,
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/changePrivacy", $getfields,"", $sandbox);

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
        'api_key' => $apiKey,
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("PUT", "domain/" . $sld . '.' . $tld . "/sendEPP", $getfields,"", $sandbox);
      
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
    $sld = idn_to_ascii($params['sld']);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        $expires = \DateTime::createFromFormat('Y-m-d H:i:s', $reply["expires_at"]);
      
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
    $sld = idn_to_ascii($params['sld']);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'api_key' => $apiKey,
    );

    try {
        $api = new ApiClient($apiKey);
        $reply = $api->call("GET", "operation/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        $status = $api->getFromResponse('status');
        $return = array();
        if ($status == "Completed") { // If transfer has completed, we'll mark it as completed and set the expiration date to be in 1 year. Sync script will update correct expiration date.
            $now = new \DateTime();
            date_add($now, date_interval_create_from_date_string('1 year'));
            $return["completed"] = true;
            $return["expirydate"] = $now->format('Y-m-d');
        } else if ($status == "Missing Authorization Code" || 
                  $status == "Transfer Rejected" || 
                  $status == "Transfer Timed Out" || 
                  $status == "Registry Transfer Request Failed" || 
                  $status == "Registrar Rejected" ||
                  $status == "Incorrect Authorization Code" || 
                  $status == "Domain is Locked" || 
                  $status == "Domain is Private" || 
                  $status == "Registry Rejected" || 
                  $status == "Domain Transferred Elsewhere" || 
                  $status == "User Cancelled" || 
                  $status == "Domain has a pendingDelete status" ||
                  $status == "Domain has a pendingTransfer status" ||
                  $status == "Time Out") {
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
    $sld = idn_to_ascii($params['sld']);
    $tld = $params['tld'];

    // Build post data
    $getfields = array(
        'api_key' => $apiKey,
    );
    logModuleCall(
            'NordName',
            "immediate_transfer_check",
            "",
            "",
            "",
            array()
        );

    try {
        $api = new ApiClient($apiKey);
        // Check if the transfer completed immediately.
        $reply = $api->call("GET", "operation/" . $sld . '.' . $tld, $getfields,"", $sandbox);
        $status = $api->getFromResponse('status');
        if ($status == "Completed") { // If transfer has completed, we'll mark it as completed and set the expiration date to be in 1 year. Sync script will update correct expiration date.
            // Get expiration date of domain and update.
            $reply = $api->call("GET", "domain/" . $sld . '.' . $tld, $getfields,"", $sandbox);
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $reply["expires_at"])->format('Y-m-d');
            logModuleCall(
                'NordName',
                "immediate_transfer_check",
                "",
                $reply,
                $date,
                array()
            );
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

function nordname_get_tld_data($params, $tld, $apiKey=null) {
    if (empty($apiKey)) {
        $apiKey = nordname_get_module_settings()["api_key"];
    }
    
    $transient_key = "nordname_" . ucfirst($tld);
    // Check if we have TLD data in cache.
    $data = WHMCS\TransientData::getInstance()->retrieve($transient_key);
    if ($data) {
        $data = json_decode($data, true);
    } else {
        $sandbox = ($params['sandbox'] == "on") ? true : false;
        $api = new ApiClient($apiKey);
        $data = $api->call("GET", "domain/tld/" . $tld, array('api_key' => $apiKey), "", $sandbox);
        WHMCS\TransientData::getInstance()->store($transient_key, json_encode($data), 3600); // Save data in cache for 1 hour.
    }
    
    return $data;
}

function nordname_AdditionalDomainFields(array $params) {
    
    $tld = $params["tld"];
    
    $fields = array();
    try {
        $conditions_added = array();
        
        // Get TLD INFO. If we have the TLD info already in cache, use that.
        $data = nordname_get_tld_data($params, $tld, $params["api_key"]);
        foreach ($data["additional_contact_fields"] as $r) {
            // Check if field_name1 or field_name2 is registrant_type. In such case, append registrant_type.
            if (($r["field_name1"] == "registrant_type" || $r["field_name2"] == "registrant_type") && !array_key_exists("registrant_type", $conditions_added)) {
                $conditions_added[] = "registrant_type";
                $fields[] = nordname_additional_fields_bank("registrant_type", $tld);
            }
            
            $split = explode(",", $r["required_fields"]);
            foreach ($split as $field) {
                $obj = nordname_additional_fields_bank($field, $tld);
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
                    $fields[] = $obj;
                }
            }
        }
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

    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );

    try {
        $api = new ApiClient($apiKey);
        // First get the list of TLDs supported by NordName.
        $reply = $api->call("GET", "domain/tld", $getfields,"", $sandbox);
        // Reply should contain an array of TLDs.
        
        $results = new ResultsList;
        
        foreach ($reply as $tld) {
            // Get each TLD info.
            $tld = $api->call("GET", "domain/tld/" . $tld, $getfields,"", $sandbox);
            
            // If admin has set the setting to only set 1 year prices, override years array with minimum value of years.
            $registration_years = $tld["technical"]["registration_years"];
            if ($params["price_sync_one_year"] == "on") {
                $registration_years = min($tld["technical"]["registration_years"]);
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

