# whmcs-domain-module
WHMCS Registrar Module for the NordName Domain API v1.1

Required PHP extension: php-intl

# Installation

1. Copy the files and folders within the repository to a folder modules/registrars/nordname within your WHMCS installation.
2. Go to Setup->Products/Services->Domain Registrars menu within your WHMCS admin area. Configure your API Key to the module settings and activate the registrar module. You're done!
3. *OPTIONAL* If you want to sell ccTLDs, also copy the resources/domains/additionalfields.php file to your WHMCS installation.
This way your customers will be requested to enter TLD specific additional information when submitting their order (e.g. ID Number or Company Registration Number)

# Supported TLDs
- All gTLDs sold by NordName
- Following ccTLDs: .me, .eu, .fi, .ax, .pw, .se, .nu, .co, .ee, .uk, .co.uk, .fr, .no, .io, .es, .gg, .de, .hu, .us, .mk, .hr, .rs, .ba
