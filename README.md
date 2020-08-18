# whmcs-domain-module
WHMCS Registrar Module for the NordName Domain API v1.2

Required PHP extension: php-intl

# Features
- Domain registrations, transfers and renewals
- Supports all TLDs sold by NordName
- TLD import & price sync support
- Dynamic additional fields (fetched from NordName API)
- Domain transfers that complete immediately also reflect immediately on WHMCS
- EXPERIMENTAL: Possible country restrictions on TLDs are shown to customer on order page

# Installation

1. Copy the files and folders within the repository to a folder modules/registrars/nordname within your WHMCS installation.
2. Go to Setup->Products/Services->Domain Registrars menu within your WHMCS admin area. Configure your API Key to the module settings, check any optional settings (descriptions of them are below), activate the registrar module and you are done!

*WHMCS 7.7 or older*:  If you want to sell ccTLDs, also copy the resources/domains/additionalfields.php file to your WHMCS installation.
This way your customers will be requested to enter TLD specific additional information when submitting their order (e.g. ID Number or Company Registration Number)
