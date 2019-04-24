<?php
/**
 * NordName Domains Registrar Module
 * @copyright Copyright (c) C-Soft Ltd 2018
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/ApiClient.php';

use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Module\Registrar\NordName\ApiClient as ApiClient;

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
        'APIVersion' => '1.1',
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
        )
    );
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


    // Build post data
    $getfields = array(
        'api_key' => $apiKey,
        'years' => $years,
        'auto_renew' => $auto_renew,
        'nameservers' => implode(",", array_filter($nameservers))
    );
    
    $body = array(
        'firstname' => $firstName,
        'lastname' => $lastName,
        'address1' => $address1,
        'city' => $city,
        'state' => $stateFullName,
        'zip' => $postcode,
        'country' => $countryCode,
        'email' => $email,
        'phone' => $phoneNumberFormatted,
        'extraData' => Array()
    );
  
    // Optional params
    if (!empty($company))
      $body["company"] = $company;
  
    if (!empty($address2))
      $body["address2"] = $address2;
  
    if (!empty($params["additionalfields"]["registrant_type"]) && $params["additionalfields"]["registrant_type"] !== '0')
        $body["extraData"]["registrant_type"] = $params["additionalfields"]["registrant_type"];
  
    if (!empty($params["additionalfields"]["birthdate"]))
      $body["extraData"]["birthdate"] = $params["additionalfields"]["birthdate"];
  
    if (!empty($params["additionalfields"]["idNumber"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["idNumber"];
      
    if (!empty($params["additionalfields"]["registerNumber"]))
      $body["extraData"]["registerNumber"] = $params["additionalfields"]["registerNumber"];
      
    if (!empty($params["additionalfields"]["vatNumber"]))
      $body["extraData"]["vatNumber"] = $params["additionalfields"]["vatNumber"];
      
    if (!empty($params["additionalfields"]["es_tipo"]))
        $body["extraData"]["es_tipo"] = $params["additionalfields"]["es_tipo"];
  
    if (!empty($params["additionalfields"]["us_nc"]))
        $body["extraData"]["us_nc"] = $params["additionalfields"]["us_nc"];

    if (!empty($params["additionalfields"]["us_ap"]))
        $body["extraData"]["us_ap"] = $params["additionalfields"]["us_ap"];

    if (!empty($params["additionalfields"]["es_other"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["es_other"];
      
    if (!empty($params["additionalfields"]["es_nif"]))
      $body["extraData"]["es_nif"] = $params["additionalfields"]["es_nif"];
    
    if (!empty($params["additionalfields"]["es_nie"]))
      $body["extraData"]["es_nie"] = $params["additionalfields"]["es_nie"];
  
    if (!empty($params["additionalfields"]["hr_oib"]))
      $body["extraData"]["hr_oib"] = $params["additionalfields"]["hr_oib"];

    try {
        $api = new ApiClient();
        $reply = $api->call("POST", "register/" . $sld . '.' . $tld, $getfields,$body, $sandbox);

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
    $years = $params['regperiod'];
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
    // domain addon purchase status
    // Build post data
    $getfields = array(
        'api_key' => $apiKey,
        'authcode' => $epp,
        'auto_renew' => $auto_renew
    );
    
    $body = array(
        'firstname' => $firstName,
        'lastname' => $lastName,
        'address1' => $address1,
        'city' => $city,
        'state' => $stateFullName,
        'zip' => $postcode,
        'country' => $countryCode,
        'email' => $email,
        'phone' => $phoneNumberFormatted,
        'extraData' => Array()
    );
  
    // Optional params
    if (!empty($company))
      $body["company"] = $company;
  
    if (!empty($address2))
      $body["address2"] = $address2;

    if (!empty($params["additionalfields"]["registrant_type"]) && $params["additionalfields"]["registrant_type"] !== '0')
        $body["extraData"]["registrant_type"] = $params["additionalfields"]["registrant_type"];
  
    if (!empty($params["additionalfields"]["birthdate"]))
      $body["extraData"]["birthdate"] = $params["additionalfields"]["birthdate"];
  
    if (!empty($params["additionalfields"]["idNumber"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["idNumber"];
      
    if (!empty($params["additionalfields"]["registerNumber"]))
      $body["extraData"]["registerNumber"] = $params["additionalfields"]["registerNumber"];
      
    if (!empty($params["additionalfields"]["vatNumber"]))
      $body["extraData"]["vatNumber"] = $params["additionalfields"]["vatNumber"];

    if (!empty($params["additionalfields"]["es_tipo"]))
        $body["extraData"]["es_tipo"] = $params["additionalfields"]["es_tipo"];

    if (!empty($params["additionalfields"]["us_nc"]))
        $body["extraData"]["us_nc"] = $params["additionalfields"]["us_nc"];

    if (!empty($params["additionalfields"]["us_ap"]))
        $body["extraData"]["us_ap"] = $params["additionalfields"]["us_ap"];
    
    if (!empty($params["additionalfields"]["es_other"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["es_other"];
      
    if (!empty($params["additionalfields"]["es_nif"]))
      $body["extraData"]["es_nif"] = $params["additionalfields"]["es_nif"];
    
    if (!empty($params["additionalfields"]["es_nie"]))
      $body["extraData"]["es_nie"] = $params["additionalfields"]["es_nie"];
  
    if (!empty($params["additionalfields"]["hr_oib"]))
      $body["extraData"]["hr_oib"] = $params["additionalfields"]["hr_oib"];

    try {
        $api = new ApiClient();
        $reply = $api->call("POST", "transfer/" . $sld . '.' . $tld, $getfields,$body, $sandbox);
        
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
        $api = new ApiClient();
        $reply = $api->call("PATCH", $sld . '.' . $tld . "/renew", $getfields,"", $sandbox);

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
 * Fetch current nameservers.
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
        $api = new ApiClient();
        $reply = $api->call("GET", $sld . '.' . $tld, $getfields,"", $sandbox);
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
        $api = new ApiClient();
        $reply = $api->call("PUT", $sld . '.' . $tld . "/changeNameservers", $getfields,"", $sandbox);

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
        'api_key' => $apiKey,
    );

    try {
        $api = new ApiClient();
        $reply = $api->call("GET", $sld . '.' . $tld, $getfields,"", $sandbox);
        $registrant = $reply["contact"];

        return array(
            'Registrant' => array(
                'First Name' => $registrant["firstname"],
                'Last Name' => $registrant["lastname"],
                'Company Name' => $registrant["company"],
                'Email Address' => $registrant["email"],
                'Address 1' => $registrant["address1"],
                'Address 2' => $registrant["address2"],
                'City' => $registrant["city"],
                'State' => $registrant["state"],
                'Postcode' => $registrant["zip"],
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

    // Build post data
    $getfields = array(
        'api_key' => $apiKey
    );
  
    $body = array(
        'firstname' => $contactDetails['Registrant']['First Name'],
        'lastname' => $contactDetails['Registrant']['Last Name'],
        'address1' => $contactDetails['Registrant']['Address 1'],
        'city' => $contactDetails['Registrant']['City'],
        'state' => $contactDetails['Registrant']['State'],
        'zip' => $contactDetails['Registrant']['Postcode'],
        'country' => $contactDetails['Registrant']['Country'],
        'email' => $contactDetails['Registrant']['Email Address'],
        'phone' => $contactDetails['Registrant']['Phone Number'],
        'extraData' => Array()
    );
  
    // Optional params
    if (!empty($contactDetails['Registrant']['Company Name']))
      $body["company"] = $contactDetails['Registrant']['Company Name'];
  
    if (!empty($contactDetails['Registrant']['Address 2']))
      $body["address2"] = $contactDetails['Registrant']['Address 2'];

    if (!empty($params["additionalfields"]["registrant_type"]))
        $body["extraData"]["registrant_type"] = $params["additionalfields"]["registrant_type"];
  
    if (!empty($params["additionalfields"]["birthdate"]))
      $body["extraData"]["birthdate"] = $params["additionalfields"]["birthdate"];
  
    if (!empty($params["additionalfields"]["idNumber"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["idNumber"];
      
    if (!empty($params["additionalfields"]["registerNumber"]))
      $body["extraData"]["registerNumber"] = $params["additionalfields"]["registerNumber"];
      
    if (!empty($params["additionalfields"]["vatNumber"]))
      $body["extraData"]["vatNumber"] = $params["additionalfields"]["vatNumber"];

    if (!empty($params["additionalfields"]["es_tipo"]))
        $body["extraData"]["es_tipo"] = $params["additionalfields"]["es_tipo"];

    if (!empty($params["additionalfields"]["us_nc"]))
        $body["extraData"]["us_nc"] = $params["additionalfields"]["us_nc"];

    if (!empty($params["additionalfields"]["us_ap"]))
        $body["extraData"]["us_ap"] = $params["additionalfields"]["us_ap"];
    
    if (!empty($params["additionalfields"]["es_other"]))
      $body["extraData"]["idNumber"] = $params["additionalfields"]["es_other"];
      
    if (!empty($params["additionalfields"]["es_nif"]))
      $body["extraData"]["es_nif"] = $params["additionalfields"]["es_nif"];
    
    if (!empty($params["additionalfields"]["es_nie"]))
      $body["extraData"]["es_nie"] = $params["additionalfields"]["es_nie"];
  
    if (!empty($params["additionalfields"]["hr_oib"]))
      $body["extraData"]["hr_oib"] = $params["additionalfields"]["hr_oib"];

    try {
        $api = new ApiClient();
        $reply = $api->call("PUT", $sld . '.' . $tld . "/updateContact", $getfields,$body, $sandbox);

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
        $api = new ApiClient();
        $reply = $api->call("GET", "checkRegistrationAvailability", $getfields, "", $sandbox);
        $results = new ResultsList();
        
        foreach ($reply as $domain => $result) {
            // Instantiate a new domain search result object
            $domainArr = explode(".", $domain, 2);
            $searchResult = new SearchResult($domainArr[0], $domainArr[1]);
            // Determine the appropriate status to return
            if ($result == 'available') {
                $status = SearchResult::STATUS_NOT_REGISTERED;
            } elseif ($result == 'unavailable') {
                $status = SearchResult::STATUS_REGISTERED;
            } else {
                $status = SearchResult::STATUS_TLD_NOT_SUPPORTED;
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

function nordname_GetDomainSuggestions($params) {
  return new ResultsList();
}

/**
 * Get registrar lock status.
 *
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
        $api = new ApiClient();
        $reply = $api->call("GET", $sld . '.' . $tld, $getfields,"", $sandbox);

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
        $api = new ApiClient();
        $reply = $api->call("PUT", $sld . '.' . $tld . "/changeTransferLock", $getfields,"", $sandbox);

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
        $api = new ApiClient();
        $reply = $api->call("PUT", $sld . '.' . $tld . "/changePrivacy", $getfields,"", $sandbox);

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
        $api = new ApiClient();
        $reply = $api->call("PUT", $sld . '.' . $tld . "/sendEPP", $getfields,"", $sandbox);
      
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
        $api = new ApiClient();
        $reply = $api->call("GET", $sld . '.' . $tld, $getfields,"", $sandbox);
        $expires = \DateTime::createFromFormat('Y-m-d H:i:s', $reply["expires_at"]);
      
        $transferredout = false;
        $active = false;
        if ($reply["status"] == "Active" || $reply["status"] == "Pending Outbound Transfer")
          $active = true;
        else if ($reply["status"] == "Transferred Out")
          $transferredout = true;
      
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
        $api = new ApiClient();
        $reply = $api->call("GET", $sld . '.' . $tld, $getfields,"", $sandbox, true);
        $status = $api->getFromResponse('status');
        $description = $api->getFromResponse('description');
        $return = array();
        if ($status == "Completed") { // If transfer has completed, we'll mark it as completed and set the expiration date to be in 1 year. Sync script will update correct expiration date.
            $now = new \DateTime();
            date_add($now, date_interval_create_from_date_string('1 year'));
            $return["completed"] = true;
            $return["expirydate"] = $now->format('Y-m-d');
        } else if ($description == "Missing Authorization Code" || 
                  $description == "Transfer Rejected" || 
                  $description == "Transfer Timed Out" || 
                  $description == "Registry Transfer Request Failed" || 
                  $description == "Registrar Rejected" ||
                  $description == "Incorrect Authorization Code" || 
                  $description == "Domain is Locked" || 
                  $description == "Domain is Private" || 
                  $description == "Registry Rejected" || 
                  $description == "Domain Transferred Elsewhere" || 
                  $description == "User Cancelled" || 
                  $description == "Domain has a pendingDelete status" ||
                  $description == "Domain has a pendingTransfer status" ||
                  $description == "Time Out") {
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
