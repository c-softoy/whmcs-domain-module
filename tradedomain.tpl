{* NordName Domain Trade Template *}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$LANG.nordname_trade_title|sprintf:$sld:$tld}</h3>
            </div>
            <div class="panel-body">
                {if $error}
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{$LANG.error}</strong>: {$error}
                    </div>
                {/if}

                {* Check if trade is allowed *}
                {if !$trade_allowed}
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{$LANG.nordname_trade_fee_required_title}</strong>
                        <p>{$LANG.nordname_trade_fee_required_message}</p>
                        <p>{$LANG.nordname_trade_contact_support_message}</p>
                        <a href="submitticket.php" class="btn btn-primary">
                            <i class="fa fa-ticket"></i> {$LANG.nordname_trade_contact_support_button}
                        </a>
                    </div>
                {else}
                    {* Active Domain Trades Section *}
                    {if isset($active_trades) && !empty($active_trades)}
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                        <div class="panel panel-warning">
                            <div class="panel-heading" role="tab" id="activeTradesHeading">
                                <h4 class="panel-title">
                                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#activeTradesCollapse" aria-expanded="true" aria-controls="activeTradesCollapse">
                                        <i class="fa fa-exchange"></i> {$LANG.nordname_trade_active_trades|sprintf:count($active_trades)}
                                        <i class="fa fa-chevron-down pull-right"></i>
                                    </a>
                                </h4>
                            </div>
                            <div id="activeTradesCollapse" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="activeTradesHeading">
                            <div class="panel-body">
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <strong>{$LANG.nordname_trade_note}:</strong> {$LANG.nordname_trade_active_trades_note}
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>{$LANG.nordname_trade_table_trade_id}</th>
                                                <th>{$LANG.nordname_trade_table_status}</th>
                                                <th>{$LANG.nordname_trade_table_start_date}</th>
                                                <th>{$LANG.nordname_trade_table_action_date}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach from=$active_trades item=trade}
                                                <tr>
                                                    <td><code>{$trade.id}</code></td>
                                                    <td>
                                                        {if $trade.status == 'Pending'}
                                                            <span class="label label-warning">{$LANG.nordname_trade_status_pending}</span>
                                                        {elseif $trade.status == 'Pending at Registry'}
                                                            <span class="label label-info">{$LANG.nordname_trade_status_pending_registry}</span>
                                                        {elseif $trade.status == 'Waiting for Old Registrant'}
                                                            <span class="label label-warning">{$LANG.nordname_trade_status_waiting_old_registrant}</span>
                                                        {elseif $trade.status == 'Waiting for New Registrant'}
                                                            <span class="label label-warning">{$LANG.nordname_trade_status_waiting_new_registrant}</span>
                                                        {elseif $trade.status == 'Waiting for Transfer Key'}
                                                            <span class="label label-warning">{$LANG.nordname_trade_status_waiting_transfer_key}</span>
                                                        {elseif $trade.status == 'Pending Documents'}
                                                            <span class="label label-warning">{$LANG.nordname_trade_status_pending_documents}</span>
                                                        {else}
                                                            <span class="label label-default">{$trade.status}</span>
                                                        {/if}
                                                    </td>
                                                    <td>
                                                        {if $trade.start_date}
                                                            {$trade.start_date|date_format:'%Y-%m-%d %H:%M'}
                                                        {else}
                                                            {$LANG.nordname_trade_na}
                                                        {/if}
                                                    </td>
                                                    <td>
                                                        {if $trade.action_date}
                                                            {$trade.action_date|date_format:'%Y-%m-%d %H:%M'}
                                                        {else}
                                                            {$LANG.nordname_trade_na}
                                                        {/if}
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&domainid={$domainid}&modop=custom&a=trade" id="tradeDomainForm">
                    <input type="hidden" name="domainid" value="{$domainid}">
                    <input type="hidden" name="sld" value="{$sld}">
                    <input type="hidden" name="tld" value="{$tld}">
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>{$LANG.nordname_trade_important}</strong> {$LANG.nordname_trade_verification_note}
                    </div>

                    {* Current Registrant Information Section *}
                    {if isset($current_registrant) && $current_registrant}
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <i class="fa fa-user"></i> {$LANG.nordname_trade_current_registrant}
                                </h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-striped table-condensed">
                                            <tr>
                                                <td><strong>{$LANG.contactname}</strong></td>
                                                <td>{$current_registrant.first_name} {$current_registrant.last_name}</td>
                                            </tr>
                                            {if $current_registrant.company}
                                                <tr>
                                                    <td><strong>{$LANG.clientareacompanyname}</strong></td>
                                                    <td>{$current_registrant.company}</td>
                                                </tr>
                                            {/if}
                                            <tr>
                                                <td><strong>{$LANG.clientareaemail}</strong></td>
                                                <td>{$current_registrant.email}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$LANG.clientareaphonenumber}</strong></td>
                                                <td>{$current_registrant.phone}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-striped table-condensed">
                                            <tr>
                                                <td><strong>{$LANG.clientareaaddress1}</strong></td>
                                                <td>
                                                    {$current_registrant.address1}
                                                    {if $current_registrant.address2}<br>{$current_registrant.address2}{/if}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$LANG.clientareacity}</strong></td>
                                                <td>{$current_registrant.city}</td>
                                            </tr>
                                            {if $current_registrant.area}
                                                <tr>
                                                    <td><strong>{$LANG.clientareastate}</strong></td>
                                                    <td>{$current_registrant.area}</td>
                                                </tr>
                                            {/if}
                                            <tr>
                                                <td><strong>{$LANG.clientareapostcode}</strong></td>
                                                <td>{$current_registrant.zip_code}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$LANG.clientareacountry}</strong></td>
                                                <td>{$current_registrant.country}</td>
                                            </tr>
                                            {if $current_registrant.registrant_type}
                                                <tr>
                                                    <td><strong>{$LANG.nordname_trade_current_registrant_type}</strong></td>
                                                    <td>{$current_registrant.registrant_type}</td>
                                                </tr>
                                            {/if}
                                        </table>
                                    </div>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {$LANG.nordname_trade_current_registrant_note}
                                </div>
                            </div>
                        </div>
                    {/if}

                    <h4>{$LANG.nordname_trade_new_registrant_info}</h4>
                    <p class="text-muted">{$LANG.nordname_trade_new_registrant_description}</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="control-label">{$LANG.clientareafirstname} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{if isset($form_data.first_name)}{$form_data.first_name}{/if}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="control-label">{$LANG.clientarealastname} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{if isset($form_data.last_name)}{$form_data.last_name}{/if}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company" class="control-label">{$LANG.clientareacompanyname}</label>
                        <input type="text" class="form-control" id="company" name="company" value="{if isset($form_data.company)}{$form_data.company}{/if}">
                        <span class="help-block">{$LANG.nordname_trade_company_help}</span>
                    </div>

                    <div class="form-group">
                        <label for="address1" class="control-label">{$LANG.clientareaaddress1} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address1" name="address1" value="{if isset($form_data.address1)}{$form_data.address1}{/if}" required>
                    </div>

                    <div class="form-group">
                        <label for="address2" class="control-label">{$LANG.clientareaaddress2}</label>
                        <input type="text" class="form-control" id="address2" name="address2" value="{if isset($form_data.address2)}{$form_data.address2}{/if}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city" class="control-label">{$LANG.clientareacity} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" value="{if isset($form_data.city)}{$form_data.city}{/if}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="area" class="control-label">{$LANG.clientareastate}</label>
                                <input type="text" class="form-control" id="area" name="area" value="{if isset($form_data.area)}{$form_data.area}{/if}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zip_code" class="control-label">{$LANG.clientareapostcode} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" value="{if isset($form_data.zip_code)}{$form_data.zip_code}{/if}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country" class="control-label">{$LANG.clientareacountry} <span class="text-danger">*</span></label>
                                <select class="form-control" id="country" name="country" required>
                                    <option value="">{$LANG.clientareaselectcountry}</option>
                                    <option value="AF"{if isset($form_data.country) && $form_data.country == 'AF'} selected{/if}>Afghanistan</option>
                                    <option value="AL">Albania</option>
                                    <option value="DZ">Algeria</option>
                                    <option value="AS">American Samoa</option>
                                    <option value="AD">Andorra</option>
                                    <option value="AO">Angola</option>
                                    <option value="AI">Anguilla</option>
                                    <option value="AQ">Antarctica</option>
                                    <option value="AG">Antigua and Barbuda</option>
                                    <option value="AR">Argentina</option>
                                    <option value="AM">Armenia</option>
                                    <option value="AW">Aruba</option>
                                    <option value="AU">Australia</option>
                                    <option value="AT">Austria</option>
                                    <option value="AZ">Azerbaijan</option>
                                    <option value="BS">Bahamas</option>
                                    <option value="BH">Bahrain</option>
                                    <option value="BD">Bangladesh</option>
                                    <option value="BB">Barbados</option>
                                    <option value="BY">Belarus</option>
                                    <option value="BE">Belgium</option>
                                    <option value="BZ">Belize</option>
                                    <option value="BJ">Benin</option>
                                    <option value="BM">Bermuda</option>
                                    <option value="BT">Bhutan</option>
                                    <option value="BO">Bolivia</option>
                                    <option value="BA">Bosnia and Herzegovina</option>
                                    <option value="BW">Botswana</option>
                                    <option value="BV">Bouvet Island</option>
                                    <option value="BR">Brazil</option>
                                    <option value="IO">British Indian Ocean Territory</option>
                                    <option value="BN">Brunei Darussalam</option>
                                    <option value="BG">Bulgaria</option>
                                    <option value="BF">Burkina Faso</option>
                                    <option value="BI">Burundi</option>
                                    <option value="KH">Cambodia</option>
                                    <option value="CM">Cameroon</option>
                                    <option value="CA">Canada</option>
                                    <option value="CV">Cape Verde</option>
                                    <option value="KY">Cayman Islands</option>
                                    <option value="CF">Central African Republic</option>
                                    <option value="TD">Chad</option>
                                    <option value="CL">Chile</option>
                                    <option value="CN">China</option>
                                    <option value="CX">Christmas Island</option>
                                    <option value="CC">Cocos (Keeling) Islands</option>
                                    <option value="CO">Colombia</option>
                                    <option value="KM">Comoros</option>
                                    <option value="CG">Congo</option>
                                    <option value="CD">Congo, Democratic Republic</option>
                                    <option value="CK">Cook Islands</option>
                                    <option value="CR">Costa Rica</option>
                                    <option value="CI">Côte d'Ivoire</option>
                                    <option value="HR">Croatia</option>
                                    <option value="CU">Cuba</option>
                                    <option value="CY">Cyprus</option>
                                    <option value="CZ">Czech Republic</option>
                                    <option value="DK">Denmark</option>
                                    <option value="DJ">Djibouti</option>
                                    <option value="DM">Dominica</option>
                                    <option value="DO">Dominican Republic</option>
                                    <option value="EC">Ecuador</option>
                                    <option value="EG">Egypt</option>
                                    <option value="SV">El Salvador</option>
                                    <option value="GQ">Equatorial Guinea</option>
                                    <option value="ER">Eritrea</option>
                                    <option value="EE">Estonia</option>
                                    <option value="ET">Ethiopia</option>
                                    <option value="FK">Falkland Islands</option>
                                    <option value="FO">Faroe Islands</option>
                                    <option value="FJ">Fiji</option>
                                    <option value="FI">Finland</option>
                                    <option value="FR">France</option>
                                    <option value="GF">French Guiana</option>
                                    <option value="PF">French Polynesia</option>
                                    <option value="TF">French Southern Territories</option>
                                    <option value="GA">Gabon</option>
                                    <option value="GM">Gambia</option>
                                    <option value="GE">Georgia</option>
                                    <option value="DE">Germany</option>
                                    <option value="GH">Ghana</option>
                                    <option value="GI">Gibraltar</option>
                                    <option value="GR">Greece</option>
                                    <option value="GL">Greenland</option>
                                    <option value="GD">Grenada</option>
                                    <option value="GP">Guadeloupe</option>
                                    <option value="GU">Guam</option>
                                    <option value="GT">Guatemala</option>
                                    <option value="GG">Guernsey</option>
                                    <option value="GN">Guinea</option>
                                    <option value="GW">Guinea-Bissau</option>
                                    <option value="GY">Guyana</option>
                                    <option value="HT">Haiti</option>
                                    <option value="HM">Heard Island</option>
                                    <option value="VA">Holy See (Vatican)</option>
                                    <option value="HN">Honduras</option>
                                    <option value="HK">Hong Kong</option>
                                    <option value="HU">Hungary</option>
                                    <option value="IS">Iceland</option>
                                    <option value="IN">India</option>
                                    <option value="ID">Indonesia</option>
                                    <option value="IR">Iran</option>
                                    <option value="IQ">Iraq</option>
                                    <option value="IE">Ireland</option>
                                    <option value="IM">Isle of Man</option>
                                    <option value="IL">Israel</option>
                                    <option value="IT">Italy</option>
                                    <option value="JM">Jamaica</option>
                                    <option value="JP">Japan</option>
                                    <option value="JE">Jersey</option>
                                    <option value="JO">Jordan</option>
                                    <option value="KZ">Kazakhstan</option>
                                    <option value="KE">Kenya</option>
                                    <option value="KI">Kiribati</option>
                                    <option value="KP">Korea, North</option>
                                    <option value="KR">Korea, South</option>
                                    <option value="KW">Kuwait</option>
                                    <option value="KG">Kyrgyzstan</option>
                                    <option value="LA">Laos</option>
                                    <option value="LV">Latvia</option>
                                    <option value="LB">Lebanon</option>
                                    <option value="LS">Lesotho</option>
                                    <option value="LR">Liberia</option>
                                    <option value="LY">Libya</option>
                                    <option value="LI">Liechtenstein</option>
                                    <option value="LT">Lithuania</option>
                                    <option value="LU">Luxembourg</option>
                                    <option value="MO">Macao</option>
                                    <option value="MK">Macedonia</option>
                                    <option value="MG">Madagascar</option>
                                    <option value="MW">Malawi</option>
                                    <option value="MY">Malaysia</option>
                                    <option value="MV">Maldives</option>
                                    <option value="ML">Mali</option>
                                    <option value="MT">Malta</option>
                                    <option value="MH">Marshall Islands</option>
                                    <option value="MQ">Martinique</option>
                                    <option value="MR">Mauritania</option>
                                    <option value="MU">Mauritius</option>
                                    <option value="YT">Mayotte</option>
                                    <option value="MX">Mexico</option>
                                    <option value="FM">Micronesia</option>
                                    <option value="MD">Moldova</option>
                                    <option value="MC">Monaco</option>
                                    <option value="MN">Mongolia</option>
                                    <option value="ME">Montenegro</option>
                                    <option value="MS">Montserrat</option>
                                    <option value="MA">Morocco</option>
                                    <option value="MZ">Mozambique</option>
                                    <option value="MM">Myanmar</option>
                                    <option value="NA">Namibia</option>
                                    <option value="NR">Nauru</option>
                                    <option value="NP">Nepal</option>
                                    <option value="NL">Netherlands</option>
                                    <option value="NC">New Caledonia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="NI">Nicaragua</option>
                                    <option value="NE">Niger</option>
                                    <option value="NG">Nigeria</option>
                                    <option value="NU">Niue</option>
                                    <option value="NF">Norfolk Island</option>
                                    <option value="MP">Northern Mariana Islands</option>
                                    <option value="NO">Norway</option>
                                    <option value="OM">Oman</option>
                                    <option value="PK">Pakistan</option>
                                    <option value="PW">Palau</option>
                                    <option value="PS">Palestine</option>
                                    <option value="PA">Panama</option>
                                    <option value="PG">Papua New Guinea</option>
                                    <option value="PY">Paraguay</option>
                                    <option value="PE">Peru</option>
                                    <option value="PH">Philippines</option>
                                    <option value="PN">Pitcairn</option>
                                    <option value="PL">Poland</option>
                                    <option value="PT">Portugal</option>
                                    <option value="PR">Puerto Rico</option>
                                    <option value="QA">Qatar</option>
                                    <option value="RE">Réunion</option>
                                    <option value="RO">Romania</option>
                                    <option value="RU">Russian Federation</option>
                                    <option value="RW">Rwanda</option>
                                    <option value="BL">Saint Barthélemy</option>
                                    <option value="SH">Saint Helena</option>
                                    <option value="KN">Saint Kitts and Nevis</option>
                                    <option value="LC">Saint Lucia</option>
                                    <option value="MF">Saint Martin</option>
                                    <option value="PM">Saint Pierre and Miquelon</option>
                                    <option value="VC">Saint Vincent and the Grenadines</option>
                                    <option value="WS">Samoa</option>
                                    <option value="SM">San Marino</option>
                                    <option value="ST">Sao Tome and Principe</option>
                                    <option value="SA">Saudi Arabia</option>
                                    <option value="SN">Senegal</option>
                                    <option value="RS">Serbia</option>
                                    <option value="SC">Seychelles</option>
                                    <option value="SL">Sierra Leone</option>
                                    <option value="SG">Singapore</option>
                                    <option value="SK">Slovakia</option>
                                    <option value="SI">Slovenia</option>
                                    <option value="SB">Solomon Islands</option>
                                    <option value="SO">Somalia</option>
                                    <option value="ZA">South Africa</option>
                                    <option value="GS">South Georgia</option>
                                    <option value="ES">Spain</option>
                                    <option value="LK">Sri Lanka</option>
                                    <option value="SD">Sudan</option>
                                    <option value="SR">Suriname</option>
                                    <option value="SJ">Svalbard and Jan Mayen</option>
                                    <option value="SZ">Swaziland</option>
                                    <option value="SE">Sweden</option>
                                    <option value="CH">Switzerland</option>
                                    <option value="SY">Syrian Arab Republic</option>
                                    <option value="TW">Taiwan</option>
                                    <option value="TJ">Tajikistan</option>
                                    <option value="TZ">Tanzania</option>
                                    <option value="TH">Thailand</option>
                                    <option value="TL">Timor-Leste</option>
                                    <option value="TG">Togo</option>
                                    <option value="TK">Tokelau</option>
                                    <option value="TO">Tonga</option>
                                    <option value="TT">Trinidad and Tobago</option>
                                    <option value="TN">Tunisia</option>
                                    <option value="TR">Turkey</option>
                                    <option value="TM">Turkmenistan</option>
                                    <option value="TC">Turks and Caicos Islands</option>
                                    <option value="TV">Tuvalu</option>
                                    <option value="UG">Uganda</option>
                                    <option value="UA">Ukraine</option>
                                    <option value="AE">United Arab Emirates</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="US">United States</option>
                                    <option value="UM">United States Minor Outlying Islands</option>
                                    <option value="UY">Uruguay</option>
                                    <option value="UZ">Uzbekistan</option>
                                    <option value="VU">Vanuatu</option>
                                    <option value="VE">Venezuela</option>
                                    <option value="VN">Vietnam</option>
                                    <option value="VG">Virgin Islands, British</option>
                                    <option value="VI">Virgin Islands, U.S.</option>
                                    <option value="WF">Wallis and Futuna</option>
                                    <option value="EH">Western Sahara</option>
                                    <option value="YE">Yemen</option>
                                    <option value="ZM">Zambia</option>
                                    <option value="ZW">Zimbabwe</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="control-label">{$LANG.clientareaemail} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{if isset($form_data.email)}{$form_data.email}{/if}" required>
                        <span class="help-block">{$LANG.nordname_trade_email_help}</span>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="control-label">{$LANG.clientareaphonenumber} <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="{if isset($form_data.phone)}{$form_data.phone}{/if}" required>
                    </div>

                    <div class="form-group">
                        <label for="language" class="control-label">{$LANG.nordname_trade_language} <span class="text-danger">*</span></label>
                        <select class="form-control" id="language" name="language" required>
                            <option value="">{$LANG.nordname_trade_select_language}</option>
                            <option value="en"{if isset($form_data.language) && $form_data.language == 'en'} selected{/if}>{$LANG.nordname_trade_english}</option>
                            <option value="fi"{if isset($form_data.language) && $form_data.language == 'fi'} selected{/if}>{$LANG.nordname_trade_finnish}</option>
                            <option value="sv"{if isset($form_data.language) && $form_data.language == 'sv'} selected{/if}>{$LANG.nordname_trade_swedish}</option>
                        </select>
                        <span class="help-block">{$LANG.nordname_trade_language_help}</span>
                    </div>

                    {* Additional fields based on TLD requirements *}
                    {if isset($additional_fields) && $additional_fields}
                        <h4>{$LANG.nordname_trade_additional_requirements|sprintf:$tld}</h4>
                        <p class="text-muted">{$LANG.nordname_trade_additional_requirements_description}</p>
                        
                        {foreach from=$additional_fields item=field}
                            <div class="form-group">
                                <label for="{$field.Name}" class="control-label">
                                    {$field.DisplayName}
                                </label>
                                {if $field.Type == 'dropdown'}
                                    <select class="form-control" id="{$field.Name}" name="{$field.Name}">
                                        <option value="">{$LANG.nordname_trade_select_field|sprintf:$field.DisplayName}</option>
                                        {foreach from=','|explode:$field.Options item=option}
                                            {assign var="option_parts" value='|'|explode:$option}
                                            <option value="{$option_parts[0]}"{if isset($form_data[$field.Name]) && $form_data[$field.Name] == $option_parts[0]} selected{/if}>{$option_parts[1]}</option>
                                        {/foreach}
                                    </select>
                                {elseif $field.Type == 'text'}
                                    <input type="text" class="form-control" id="{$field.Name}" name="{$field.Name}" 
                                           value="{if isset($form_data[$field.Name])}{$form_data[$field.Name]}{/if}"
                                           {if isset($field.Size)}size="{$field.Size}"{/if}>
                                {elseif $field.Type == 'date'}
                                    <input type="date" class="form-control" id="{$field.Name}" name="{$field.Name}" 
                                           value="{if isset($form_data[$field.Name])}{$form_data[$field.Name]}{/if}">
                                {/if}
                                
                                {if isset($field.Description)}
                                    <span class="help-block">{$field.Description}</span>
                                {/if}
                            </div>
                        {/foreach}
                    {/if}

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="confirm_trade" required>
                                {$LANG.nordname_trade_confirm_checkbox}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" name="submit_trade">
                            <i class="fa fa-exchange"></i> {$LANG.nordname_trade_submit_request}
                        </button>
                        <a href="clientarea.php?action=domaindetails&domainid={$domainid}" class="btn btn-default">
                            <i class="fa fa-times"></i> {$LANG.nordname_trade_cancel}
                        </a>
                    </div>
                </form>
                {/if}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Show/hide company field based on registrant type
    $('#registrant_type').change(function() {
        var selectedType = $(this).val();
        if (selectedType === 'Private Person') {
            $('#company').closest('.form-group').hide();
        } else {
            $('#company').closest('.form-group').show();
        }
    });
    
    // Trigger change event on page load
    $('#registrant_type').trigger('change');
    
    // Initialize Bootstrap collapse functionality
    $('.collapse').collapse({
        toggle: false
    });
    
    // Handle collapse icon rotation
    $('.panel-title a').on('click', function() {
        var $icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
        if ($icon.hasClass('fa-chevron-down')) {
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });
    
    // Form validation
    $('#tradeDomainForm').submit(function(e) {
        var isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('{$LANG.nordname_trade_validation_error}');
            return false;
        }
        
        // Confirm submission
        if (!confirm('{$LANG.nordname_trade_js_confirm_submission}')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>