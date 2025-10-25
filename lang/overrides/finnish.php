<?php

if (!defined("WHMCS")) die("This file cannot be accessed directly");

$_LANG['tabChangeOwner'] = "Vaihda omistaja";
$_LANG['tabDomainDNS'] = "DNS-tietueet";
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

$_LANG['Registrant Type'] = "Haltijan tyyppi";
$_LANG['Private Person'] = 'Yksityishenkilö';
$_LANG['Company'] = 'Yritys';
$_LANG['Association'] = 'Yhdistys';
$_LANG['Institution'] = 'Säätiö';
$_LANG['Political Party'] = 'Puolue';
$_LANG['Township'] = 'Kunta';
$_LANG['Government'] = 'Valtio';
$_LANG['Public Community'] = 'Yhteisö';
$_LANG['Country of Citizenship'] = "Kansalaisuus";
$_LANG['ID Number'] = "Henkilötunnus";
$_LANG['Business registration number'] = "Y-tunnus";
$_LANG['Birth Date'] = "Syntymäpäivä";
$_LANG['VAT Number'] = "ALV-numero";
$_LANG['Private persons: ID card or passport number'] = "Yksityishenkilöille: Suomalainen henkilötunnus (henkilötunnus)";
$_LANG['Business customers: Company registration number'] = "Yritysasiakkaille: Y-tunnus";
$_LANG['For private persons not resident in Finland: Birth date in format YYYY-MM-DD'] = "Vain yksityishenkilöille, joilla ei ole henkilötunnusta. Muodossa YYYY-MM-DD";

// DNS Management
$_LANG['nordname_dns_management_title'] = "DNS-hallinta";
$_LANG['nordname_dns_management_disabled_error'] = "DNS-hallinta ei ole käytössä tälle verkkotunnukselle.";
$_LANG['nordname_dns_info_title'] = "Tietoa DNS-hallinnasta";
$_LANG['nordname_dns_info_message'] = "Hallitse verkkotunnuksiesi DNS-tietueita. Muutoksien voimaantulossa voi kestää 24-48 tuntia.";
$_LANG['nordname_dns_info_ns'] = "Tietueet ovat voimassa vain jos käytät oletusnimipalvelimiamme.";
$_LANG['nordname_dns_info_ttl'] = "TTL (Time To Live) on sekunneissa. Oletus on 3600 (1 tunti)";
$_LANG['nordname_dns_info_hostname'] = "Käytä @ tai jätä tyhjäksi lisätäksesi tietueen verkkotunnuksen juureen.";
$_LANG['nordname_dns_info_soa'] = "SOA- ja NS-tietueet on järjestelmän hallinnoimia, eikä niitä voi muuttaa.";
$_LANG['nordname_dns_current_records'] = "Nykyiset DNS-tietueet";
$_LANG['nordname_dns_hostname'] = "Nimi";
$_LANG['nordname_dns_type'] = "Tyyppi";
$_LANG['nordname_dns_content'] = "Sisältö";
$_LANG['nordname_dns_priority'] = "Prioriteetti";
$_LANG['nordname_dns_ttl'] = "TTL";
$_LANG['nordname_dns_actions'] = "Toiminnot";
$_LANG['nordname_dns_delete'] = "Poista";
$_LANG['nordname_dns_delete_confirm'] = "Haluatko varmasti poistaa tämän DNS-tietueen?";
$_LANG['nordname_dns_system_record'] = "Järjestelmä";
$_LANG['nordname_dns_no_records'] = "DNS-tietueita ei löydetty. Lisää omasi alta.";
$_LANG['nordname_dns_add_record'] = "Lisää uusi DNS-tietue";
$_LANG['nordname_dns_hostname_help'] = "Syötä @ juurta varten tai aliverkkotunnus (esim. www).";
$_LANG['nordname_dns_select_type'] = "Valitse tietueen tyyppi";
$_LANG['nordname_dns_content_help'] = "Tietueen arvo (IP-osoite, isäntäpalvelin, teksti, jne.)";
$_LANG['nordname_dns_priority_help'] = "Alhaisemmilla arvoilla on korkeampi prioriteetti (vaaditaan MX- ja SRV-tietueille).";
$_LANG['nordname_dns_ttl_help'] = "Time To Live - kauanko nimipalvelimet pitävät tietueen välimuistissa.";
$_LANG['nordname_dns_add_record_button'] = "Lisää DNS-tietue";
$_LANG['nordname_dns_back'] = "Takaisin verkkotunnuksen tietoihin";
$_LANG['nordname_dns_record_added'] = "DNS-tietue lisätty onnistuneesti";
$_LANG['nordname_dns_record_deleted'] = "DNS-tietue poistettu onnistuneesti";
