{* NordName DNS Management Template *}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-globe"></i> {$LANG.nordname_dns_management_title|default:"DNS Management"} - {$domain}
                </h3>
            </div>
            <div class="panel-body">
                {if $error}
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{$LANG.error|default:"Error"}</strong>: {$error}
                    </div>
                {/if}
                
                {if $success}
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i>
                        {$success}
                    </div>
                {/if}

                {if $dnsdisabled}
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{$LANG.nordname_dns_management_disabled_error|default:"DNS management is not active for this domain."}</strong>
                    </div>
                {else}

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>{$LANG.nordname_dns_info_title|default:"DNS Management Information"}</strong>
                    <p>{$LANG.nordname_dns_info_message|default:"Manage DNS records for your domain. Changes may take up to 24-48 hours to propagate globally."}</p>
                    <ul>
                        <li>{$LANG.nordname_dns_info_ns|default:"Records are only in use if you are using our default name servers."}</li>
                        <li>{$LANG.nordname_dns_info_ttl|default:"TTL (Time To Live) is in seconds. Default is 3600 (1 hour)"}</li>
                        <li>{$LANG.nordname_dns_info_hostname|default:"Use @ or leave empty for root domain records"}</li>
                        <li>{$LANG.nordname_dns_info_soa|default:"SOA and NS records are managed automatically and cannot be modified"}</li>
                    </ul>
                </div>

                {* Current DNS Records *}
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-list"></i> {$LANG.nordname_dns_current_records|default:"Current DNS Records"}
                        </h4>
                    </div>
                    <div class="panel-body">
                        {if $dnsrecords && count($dnsrecords) > 0}
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>{$LANG.nordname_dns_hostname|default:"Hostname"}</th>
                                            <th>{$LANG.nordname_dns_type|default:"Type"}</th>
                                            <th>{$LANG.nordname_dns_content|default:"Content"}</th>
                                            <th>{$LANG.nordname_dns_priority|default:"Priority"}</th>
                                            <th>{$LANG.nordname_dns_ttl|default:"TTL"}</th>
                                            <th>{$LANG.nordname_dns_actions|default:"Actions"}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$dnsrecords item=record}
                                            <tr>
                                                <td>
                                                    <code>{$record.name}</code>
                                                </td>
                                                <td>
                                                    <span class="label label-info">{$record.type}</span>
                                                </td>
                                                <td>
                                                    <small>{$record.content}</small>
                                                </td>
                                                <td>
                                                    {if $record.priority}
                                                        {$record.priority}
                                                    {else}
                                                        <span class="text-muted">-</span>
                                                    {/if}
                                                </td>
                                                <td>{$record.ttl}s</td>
                                                <td>
                                                    {* Don't allow deletion of SOA and NS records *}
                                                    {if $record.type != 'SOA' && $record.type != 'NS'}
                                                        <form method="post" style="display: inline;" onsubmit="return confirm('{$LANG.nordname_dns_delete_confirm|default:"Are you sure you want to delete this DNS record?"}');">
                                                            <input type="hidden" name="record_id" value="{$record.id}" />
                                                            <button type="submit" name="delete_record" class="btn btn-xs btn-danger">
                                                                <i class="fa fa-trash"></i> {$LANG.nordname_dns_delete|default:"Delete"}
                                                            </button>
                                                        </form>
                                                    {else}
                                                        <span class="text-muted">{$LANG.nordname_dns_system_record|default:"System"}</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-circle"></i>
                                {$LANG.nordname_dns_no_records|default:"No DNS records found. Add your first record below."}
                            </div>
                        {/if}
                    </div>
                </div>

                {* Add New DNS Record Form *}
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-plus-circle"></i> {$LANG.nordname_dns_add_record|default:"Add New DNS Record"}
                        </h4>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&domainid={$domainid}&modop=custom&a=domaindns" class="form-horizontal">
                            <input type="hidden" name="domainid" value="{$domainid}">
                            
                            <div class="form-group">
                                <label for="record_name" class="col-sm-3 control-label">
                                    {$LANG.nordname_dns_hostname|default:"Hostname"}
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="record_name" id="record_name" class="form-control" placeholder="@ or subdomain">
                                    <span class="help-block">{$LANG.nordname_dns_hostname_help|default:"Use @ for root domain or enter subdomain name (e.g., www, mail)"}</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="record_type" class="col-sm-3 control-label">
                                    {$LANG.nordname_dns_type|default:"Type"} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="record_type" id="record_type" class="form-control" required onchange="togglePriorityField()">
                                        <option value="">{$LANG.nordname_dns_select_type|default:"Select record type"}</option>
                                        <option value="A">A - IPv4 Address</option>
                                        <option value="AAAA">AAAA - IPv6 Address</option>
                                        <option value="CNAME">CNAME - Canonical Name</option>
                                        <option value="MX">MX - Mail Exchange</option>
                                        <option value="TXT">TXT - Text Record</option>
                                        <option value="SRV">SRV - Service Record</option>
                                        <option value="CAA">CAA - Certificate Authority Authorization</option>
                                        <option value="ALIAS">ALIAS - ALIAS Record</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="record_content" class="col-sm-3 control-label">
                                    {$LANG.nordname_dns_content|default:"Content"} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="record_content" id="record_content" class="form-control" required placeholder="e.g., 192.0.2.1 or example.com">
                                    <span class="help-block">{$LANG.nordname_dns_content_help|default:"Record value (IP address, hostname, text, etc.)"}</span>
                                </div>
                            </div>
                            
                            <div class="form-group" id="priority_group" style="display: none;">
                                <label for="record_priority" class="col-sm-3 control-label">
                                    {$LANG.nordname_dns_priority|default:"Priority"}
                                </label>
                                <div class="col-sm-9">
                                    <input type="number" name="record_priority" id="record_priority" class="form-control" placeholder="10" min="0" max="65535">
                                    <span class="help-block">{$LANG.nordname_dns_priority_help|default:"Lower values have higher priority (required for MX and SRV records)"}</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="record_ttl" class="col-sm-3 control-label">
                                    {$LANG.nordname_dns_ttl|default:"TTL"}
                                </label>
                                <div class="col-sm-9">
                                    <select name="record_ttl" id="record_ttl" class="form-control">
                                        <option value="3600" selected>1 hour (3600)</option>
                                        <option value="7200">2 hours (7200)</option>
                                        <option value="14400">4 hours (14400)</option>
                                        <option value="28800">8 hours (28800)</option>
                                        <option value="43200">12 hours (43200)</option>
                                        <option value="86400">1 day (86400)</option>
                                    </select>
                                    <span class="help-block">{$LANG.nordname_dns_ttl_help|default:"Time To Live - how long resolvers should cache this record"}</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" name="add_record" class="btn btn-success">
                                        <i class="fa fa-plus"></i> {$LANG.nordname_dns_add_record_button|default:"Add DNS Record"}
                                    </button>
                                    <a href="clientarea.php?action=domaindetails&id={$domainid}" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> {$LANG.nordname_dns_back|default:"Back to Domain Details"}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function togglePriorityField() {
    var recordType = document.getElementById('record_type').value;
    var priorityGroup = document.getElementById('priority_group');
    var priorityInput = document.getElementById('record_priority');
    
    if (recordType === 'MX' || recordType === 'SRV') {
        priorityGroup.style.display = 'block';
        if (recordType === 'MX' || recordType === 'SRV') {
            priorityInput.required = true;
        }
    } else {
        priorityGroup.style.display = 'none';
        priorityInput.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePriorityField();
});
</script>
