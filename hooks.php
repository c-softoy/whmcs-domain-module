<?php /** @noinspection PhpInconsistentReturnPointsInspection */
if(!defined('WHMCS'))
    die('This file cannot be accessed directly');

require_once __DIR__ . '/nordname.php';

add_hook('ClientAreaHeadOutput', 50, function($vars) {
    if($vars['templatefile'] !== 'configuredomains')
        return;
        
    $settings = nordname_get_module_settings();
        
    $domains = [];
    // First find domains in cart that have NordName set as registrar.
    foreach ($_SESSION['cart']['domains'] as $key => $domain) {
        $split = explode(".", $domain["domain"], 2);
        if (count($split) == 2) {
            $tld = $split[1];
            if (nordname_get_registrar_for_tld($tld) == "nordname") {
                $domains[] = array("domain" => $domain["domain"], "tld" => $tld, "key" => $key);
            }
        }
    }
    
    ob_start();
    ?>
    <script type="text/javascript">
    $(document).ready(function() {
    <?php
    
    // Then, for these domains, check if the TLDs have country restrictions and show warning text.
    if ($settings["display_restrictions"]) {
        foreach ($domains as $obj) {
            $tld = nordname_get_tld_data($obj["tld"], $settings["api_key"]);
            if (!empty($tld["legal"]["registrant_countries"])) {
                ?>
                $("#frmConfigureDomains .sub-heading").eq(<?= $obj["key"] ?>).after("<p>If you do not fulfill this requirement, there may be a trustee service available. Contact us.</p>");
                $("#frmConfigureDomains .sub-heading").eq(<?= $obj["key"] ?>).after("<p><?=\Lang::trans('Registrations under this Top Level Domain require you to be a resident in one of the following countries: ') . implode(",", $tld["legal"]["registrant_countries"])?></p>");

                <?php
            }
        }
    }
    
    ?>
    });
    </script>
    <?php
    return ob_get_clean();
});

/**
 * Certain NordName transfers execute immediately.
 *
 * This will immediately check tranfer status after it has been submitted, and if applicable,
 * update domain status to Active
 */
add_hook('AfterRegistrarTransfer', 50, function($vars) {
    if(@$vars['params']['registrar'] !== 'nordname') {
        return;
    }

    /** @noinspection UnusedFunctionResultInspection */
    RegCallFunction($vars['params'], 'ImmediateTransferCheck');
});

?>
