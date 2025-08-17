<?php

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (!function_exists("nordname_get_tld_data")) {
    require ROOTDIR . "/modules/registrars/nordname/nordname.php";
}

$tld = App::getFromRequest("tld");

if (!ctype_alpha($tld)) {
    return array("result" => "error", "message" => "Invalid TLD");
}

$tld_data = nordname_get_tld_data($tld, $params);
$apiresults = array_merge(array("result" => "success"), $tld_data);

?>