<?php

if (!defined("WHMCS")) die("This file cannot be accessed directly");

$_LANG['tabChangeOwner'] = "Change owner";
$_LANG['tabDomainDNS'] = "DNS records";
// Page Title and Headers
$_LANG['nordname_trade_title'] = "Change Domain Owner - %s.%s";
$_LANG['nordname_trade_new_registrant_info'] = "New Registrant Information";
$_LANG['nordname_trade_additional_requirements'] = "Additional Requirements for .%s";

// Error Messages
$_LANG['nordname_trade_validation_error'] = "Please fill in all required fields";
$_LANG['nordname_trade_confirm_submission'] = "Are you sure you want to submit this domain trade request? This action cannot be undone.";

// Active Domain Trades Section
$_LANG['nordname_trade_active_trades'] = "Active Domain Trades (%d)";
$_LANG['nordname_trade_active_trades_note'] = "Note: You have active domain trade requests. Please review them before initiating a new trade.";
$_LANG['nordname_trade_table_trade_id'] = "Trade ID";
$_LANG['nordname_trade_table_status'] = "Status";
$_LANG['nordname_trade_table_start_date'] = "Start Date";
$_LANG['nordname_trade_table_action_date'] = "Action Date";
$_LANG['nordname_trade_status_pending'] = "Pending";
$_LANG['nordname_trade_status_pending_registry'] = "Pending at Registry";
$_LANG['nordname_trade_status_waiting_old_registrant'] = "Waiting for Old Registrant";
$_LANG['nordname_trade_status_waiting_new_registrant'] = "Waiting for New Registrant";
$_LANG['nordname_trade_status_waiting_transfer_key'] = "Waiting for Transfer Key";
$_LANG['nordname_trade_status_pending_documents'] = "Pending Documents";
$_LANG['nordname_trade_na'] = "N/A";

// Important Information Alerts
$_LANG['nordname_trade_important'] = "Important:";
$_LANG['nordname_trade_verification_note'] = "Changing the domain owner (registrant) may require verification from the old registrant and new registrant.";

// Current Registrant Section
$_LANG['nordname_trade_current_registrant'] = "Current Domain Registrant";
$_LANG['nordname_trade_current_registrant_note'] = "Note: The current registrant may receive an email notification about this ownership change request and may need to approve it. Ensure that the contact details of current owner are up-to-date. If they are not, update them through the \"Contact Information\" page.";

// Form Labels and Help Text
$_LANG['nordname_trade_new_registrant_description'] = "Please provide the complete information for the new domain owner (registrant).";
$_LANG['nordname_trade_email_help'] = "The new registrant will receive verification emails at this address";
$_LANG['nordname_trade_language'] = "Language";
$_LANG['nordname_trade_select_language'] = "Select Language";
$_LANG['nordname_trade_language_help'] = "Language for email communications";
$_LANG['nordname_trade_english'] = "English";
$_LANG['nordname_trade_finnish'] = "Finnish";
$_LANG['nordname_trade_swedish'] = "Swedish";

// Additional Fields Section
$_LANG['nordname_trade_additional_requirements_description'] = "The following additional information is required for this domain extension.";
$_LANG['nordname_trade_select_field'] = "Select %s";

// Current Registrant Table Label
$_LANG['nordname_trade_current_registrant_type'] = "Registrant Type";

// Form Buttons
$_LANG['nordname_trade_submit_request'] = "Submit Domain Trade Request";
$_LANG['nordname_trade_cancel'] = "Cancel";

// Feedback
$_LANG["nordname_trade_completed"] = "Domain trade has been completed successfully! The domain ownership has been transferred to the new registrant.";
$_LANG["nordname_trade_processing"] = "Domain trade request has been submitted for processing. You may shortly receive a verification email that you need to action.";

// Confirmation Checkbox
$_LANG['nordname_trade_confirm_checkbox'] = "I confirm that I have the authority to transfer ownership of this domain and that all information provided is accurate.";

// JavaScript Messages
$_LANG['nordname_trade_js_confirm_submission'] = "Are you sure you want to submit this domain trade request? This action cannot be undone.";

// Success Template Variables
$_LANG['nordname_trade_success_title'] = "Domain Trade Request Submitted";
$_LANG['nordname_trade_success_heading'] = "Success!";
$_LANG['nordname_trade_success_domain_info'] = "Domain Information";
$_LANG['nordname_trade_success_domain'] = "Domain";
$_LANG['nordname_trade_success_status'] = "Status";
$_LANG['nordname_trade_success_completed'] = "Completed";
$_LANG['nordname_trade_success_trade_id'] = "Trade ID";
$_LANG['nordname_trade_success_next_steps'] = "Next Steps";
$_LANG['nordname_trade_success_next_steps_1'] = "The old and new registrant may receive email confirmations";
$_LANG['nordname_trade_success_next_steps_2'] = "They must approve the transfer by clicking the link in the email";
$_LANG['nordname_trade_success_next_steps_3'] = "The process may take several days to complete";
$_LANG['nordname_trade_success_next_steps_4'] = "You will be notified via email once the transfer is complete";
$_LANG['nordname_trade_success_back_to_domain'] = "Back to Domain Details";
$_LANG['nordname_trade_success_view_all_domains'] = "View All Domains";

// Additional Trade Template Variables
$_LANG['nordname_trade_note'] = "Note";
$_LANG['nordname_trade_company_help'] = "Leave blank for private individuals";

// Trade Fee Restriction Messages
$_LANG['nordname_trade_fee_required_title'] = "Domain Trade Requires Payment";
$_LANG['nordname_trade_fee_required_message'] = "Domain trade operations for this TLD require payment of extra fee. Free domain trades are not available for this domain extension.";
$_LANG['nordname_trade_contact_support_message'] = "To request a domain trade with payment, please contact our support team.";
$_LANG['nordname_trade_contact_support_button'] = "Contact Support";

// DNS Management
$_LANG['nordname_dns_management_title'] = "DNS Management";
$_LANG['nordname_dns_management_disabled_error'] = "DNS management is not active for this domain.";
$_LANG['nordname_dns_info_title'] = "DNS Management Information";
$_LANG['nordname_dns_info_message'] = "Manage DNS records for your domain. Changes may take up to 24-48 hours to propagate globally.";
$_LANG['nordname_dns_info_ns'] = "Records are only in use if you are using our default name servers.";
$_LANG['nordname_dns_info_ttl'] = "TTL (Time To Live) is in seconds. Default is 3600 (1 hour)";
$_LANG['nordname_dns_info_hostname'] = "Use @ or leave empty for root domain records";
$_LANG['nordname_dns_info_soa'] = "SOA and NS records are managed automatically and cannot be modified";
$_LANG['nordname_dns_current_records'] = "Current DNS Records";
$_LANG['nordname_dns_hostname'] = "Hostname";
$_LANG['nordname_dns_type'] = "Type";
$_LANG['nordname_dns_content'] = "Content";
$_LANG['nordname_dns_priority'] = "Priority";
$_LANG['nordname_dns_ttl'] = "TTL";
$_LANG['nordname_dns_actions'] = "Actions";
$_LANG['nordname_dns_delete'] = "Delete";
$_LANG['nordname_dns_delete_confirm'] = "Are you sure you want to delete this DNS record?";
$_LANG['nordname_dns_system_record'] = "System";
$_LANG['nordname_dns_no_records'] = "No DNS records found. Add your first record below.";
$_LANG['nordname_dns_add_record'] = "Add New DNS Record";
$_LANG['nordname_dns_hostname_help'] = "Use @ for root domain or enter subdomain name (e.g., www, mail)";
$_LANG['nordname_dns_select_type'] = "Select record type";
$_LANG['nordname_dns_content_help'] = "Record value (IP address, hostname, text, etc.)";
$_LANG['nordname_dns_priority_help'] = "Lower values have higher priority (required for MX and SRV records)";
$_LANG['nordname_dns_ttl_help'] = "Time To Live - how long resolvers should cache this record";
$_LANG['nordname_dns_add_record_button'] = "Add DNS Record";
$_LANG['nordname_dns_back'] = "Back to Domain Details";
$_LANG['nordname_dns_record_added'] = "DNS record added successfully";
$_LANG['nordname_dns_record_deleted'] = "DNS record deleted successfully";