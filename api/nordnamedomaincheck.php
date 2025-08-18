<?php

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}

if (!function_exists("nordname_get_module_settings")) {
    require ROOTDIR . "/modules/registrars/nordname/nordname.php";
}

$params = nordname_get_module_settings();

$domains = App::getFromRequest("domains");

if (empty($domains)) {
    $apiresults = array("result" => "error", "message" => "No domains provided");
    return;
}


$getfields = array(
    'domain' => $domains,
);

try {
    $api = new WHMCS\Module\Registrar\NordName\ApiClient($params["api_key"], $params["sandbox"]);
    $reply = $api->call("GET", "domain/availability", $getfields);
    $results = array();
    
    foreach ($reply as $result) {
        $domainName = $result['domain'];
        $domainArr = explode('.', $domainName, 2);
        if (count($domainArr) !== 2) {
            continue;
        }

        $results[$domainName] = $result['is_premium'] ? false : $result['avail'];
    }

    $apiresults = array("result" => "success", "domains" => $results);

} catch (\Exception $e) {
    return array(
        'error' => $e->getMessage(),
    );
}


?>