<?php

if (!defined("WHMCS")) die("This file cannot be accessed directly");

$_LANG['tabChangeOwner'] = "Vaihda omistaja";
// Page Title and Headers
$_LANG['nordname_trade_title'] = "Vaihda omistaja - %s.%s";
$_LANG['nordname_trade_new_registrant_info'] = "Uuden omistajan tiedot";
$_LANG['nordname_trade_additional_requirements'] = "Lisätiedot .%s";

// Error Messages
$_LANG['nordname_trade_validation_error'] = "Täytä kaikki pakolliset kentät";
$_LANG['nordname_trade_confirm_submission'] = "Oletko varma, että haluat pyytää omistajan vaihtoa? Tätä toimintoa ei voi perua.";

// Active Domain Trades Section
$_LANG['nordname_trade_active_trades'] = "Aktiiviset omistajan vaihdot (%d)";
$_LANG['nordname_trade_active_trades_note'] = "Huom: Sinulla on käynnissä olevia omistajan vaihtoja. Käythän ne läpi ennen kuin tilaat uuden.";
$_LANG['nordname_trade_table_trade_id'] = "ID";
$_LANG['nordname_trade_table_status'] = "Tila";
$_LANG['nordname_trade_table_start_date'] = "Alkupvm";
$_LANG['nordname_trade_table_action_date'] = "Deadline";
$_LANG['nordname_trade_status_pending'] = "Odottaa";
$_LANG['nordname_trade_status_pending_registry'] = "Odottaa rekisteriä";
$_LANG['nordname_trade_status_waiting_old_registrant'] = "Odottaa vanhaa omistajaa";
$_LANG['nordname_trade_status_waiting_new_registrant'] = "Odottaa uutta omistajaa";
$_LANG['nordname_trade_status_waiting_transfer_key'] = "Odottaa siirtokoodia";
$_LANG['nordname_trade_status_pending_documents'] = "Odottaa asiakirjoja";
$_LANG['nordname_trade_na'] = "Ei saatavilla";

// Important Information Alerts
$_LANG['nordname_trade_important'] = "Tärkeää:";
$_LANG['nordname_trade_verification_note'] = "Omistajan vaihto voi vaatia vahvistuksen vanhalta ja uudelta omistajalta.";

// Current Registrant Section
$_LANG['nordname_trade_current_registrant'] = "Nykyinen omistaja";
$_LANG['nordname_trade_current_registrant_note'] = "Huom: Nykyinen omistaja saattaa vastaanottaa vahvistuspyynnön sähköpostitse. Tarkista, että omistajan sähköpostiosoite on ajantasainen.";

// Form Labels and Help Text
$_LANG['nordname_trade_new_registrant_description'] = "Täytä uuden omistajan tiedot";
$_LANG['nordname_trade_email_help'] = "Uusi omistaja saa vahvistusviestit tähän sähköpostiosoitteeseen";
$_LANG['nordname_trade_language'] = "Kieli";
$_LANG['nordname_trade_select_language'] = "Valitse kieli";
$_LANG['nordname_trade_language_help'] = "Sähköpostiviestien kieli";
$_LANG['nordname_trade_english'] = "Englanti";
$_LANG['nordname_trade_finnish'] = "Suomi";
$_LANG['nordname_trade_swedish'] = "Ruotsi";

// Additional Fields Section
$_LANG['nordname_trade_additional_requirements_description'] = "Seuraavat lisätiedot ovat vaaditaan tälle verkkotunnuspäätteelle.";
$_LANG['nordname_trade_select_field'] = "Valitse %s";

// Current Registrant Table Label
$_LANG['nordname_trade_current_registrant_type'] = "Haltijan tyyppi";

// Form Buttons
$_LANG['nordname_trade_submit_request'] = "Lähetä siirtopyyntö";
$_LANG['nordname_trade_cancel'] = "Peru";

// Feedback
$_LANG["nordname_trade_completed"] = "Omistajan vaihto on suoritettu!";
$_LANG["nordname_trade_processing"] = "Omistajan vaihto pyyntö on lähetetty käsiteltäväksi. Vanha ja uusi omistaja saattavat joutua vahvistamaan pyynnön.";

// Confirmation Checkbox
$_LANG['nordname_trade_confirm_checkbox'] = "Vahvistan, että minulla on oikeus siirtää tämä verkkotunnuksen omistajuus ja että kaikki syötetyt tiedot ovat oikein.";

// JavaScript Messages
$_LANG['nordname_trade_js_confirm_submission'] = "Oletko varma, että haluat lähettää tämän pyynnön? Tätä toimintoa ei voi perua.";

// Success Template Variables
$_LANG['nordname_trade_success_title'] = "Omistajan vaihtopyyntö lähetetty";
$_LANG['nordname_trade_success_heading'] = "Onnistui!";
$_LANG['nordname_trade_success_domain_info'] = "Verkkotunnuksen tiedot";
$_LANG['nordname_trade_success_domain'] = "Verkkotunnus";
$_LANG['nordname_trade_success_status'] = "Tila";
$_LANG['nordname_trade_success_completed'] = "Valmis";
$_LANG['nordname_trade_success_trade_id'] = "ID";
$_LANG['nordname_trade_success_next_steps'] = "Seuraavat vaiheet";
$_LANG['nordname_trade_success_next_steps_1'] = "Vanha ja uusi omistaja saattavat vastaanottaa vahvistusviestit";
$_LANG['nordname_trade_success_next_steps_2'] = "Heidän on hyväksyttävä siirto painamalla sähköpostissa olevaa linkkiä";
$_LANG['nordname_trade_success_next_steps_3'] = "Prosessi voi kestää useita päiviä";
$_LANG['nordname_trade_success_next_steps_4'] = "Sinulle ilmoitetaan sähköpostitse, kun siirto on valmis";
$_LANG['nordname_trade_success_back_to_domain'] = "Takaisin verkkotunnuksen tietoihin";
$_LANG['nordname_trade_success_view_all_domains'] = "Kaikki verkkotunnukset";

// Additional Trade Template Variables
$_LANG['nordname_trade_note'] = "Huom";
$_LANG['nordname_trade_company_help'] = "Jätä tyhjäksi yksityishenkilöille";

// Trade Fee Restriction Messages
$_LANG['nordname_trade_fee_required_title'] = "Omistajan vaihto on maksullinen";
$_LANG['nordname_trade_fee_required_message'] = "Tällä verkkotunnuspäätteellä omistajan vaihdot ovat maksullisia.";
$_LANG['nordname_trade_contact_support_message'] = "Pyydä omistajan vaihto asiakaspalvelun kautta.";
$_LANG['nordname_trade_contact_support_button'] = "Ota yhteyttä asiakaspalveluun";