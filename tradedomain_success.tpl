{* NordName Domain Trade Success Template *}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-check-circle"></i> {$LANG.nordname_trade_success_title}
                </h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-success">
                    <h4><i class="fa fa-check-circle"></i> {$LANG.nordname_trade_success_heading}</h4>
                    <p>{$success_message}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4>{$LANG.nordname_trade_success_domain_info}</h4>
                        <table class="table table-striped">
                            <tr>
                                <td><strong>{$LANG.nordname_trade_success_domain}:</strong></td>
                                <td>{$sld}.{$tld}</td>
                            </tr>
                            <tr>
                                <td><strong>{$LANG.nordname_trade_success_status}:</strong></td>
                                <td>

                                    {if $is_completed}
                                        <span class="label label-success">{$LANG.nordname_trade_success_completed}</span>
                                    {else}
                                        <span class="label label-default">{$trade_status}</span>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($trade_response.id)}
                                <tr>
                                    <td><strong>{$LANG.nordname_trade_success_trade_id}:</strong></td>
                                    <td>{$trade_response.id}</td>
                                </tr>
                            {/if}
                        </table>
                    </div>
                    {if !$is_completed}
                        <div class="col-md-6">
                            <h4>{$LANG.nordname_trade_success_next_steps}</h4>
                            <ul>
                                <li>{$LANG.nordname_trade_success_next_steps_1}</li>
                                <li>{$LANG.nordname_trade_success_next_steps_2}</li>
                                <li>{$LANG.nordname_trade_success_next_steps_3}</li>
                                <li>{$LANG.nordname_trade_success_next_steps_4}</li>
                            </ul>
                        </div>
                    {/if}
                </div>

                <div class="form-group">
                    <a href="clientarea.php?action=domaindetails&domainid={$domainid}" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> {$LANG.nordname_trade_success_back_to_domain}
                    </a>
                    <a href="clientarea.php?action=domains" class="btn btn-default">
                        <i class="fa fa-list"></i> {$LANG.nordname_trade_success_view_all_domains}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
