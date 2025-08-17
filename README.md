# whmcs-domain-module
WHMCS Registrar Module for the NordName Domain API v3.0

Required PHP extension: php-intl

# Features
- Domain registrations, transfers and renewals
- Supports all TLDs sold by NordName
- TLD import & price sync support
- Dynamic additional fields (fetched from NordName API)
- Domain transfers that complete immediately also reflect immediately on WHMCS
- Separate domain ownership change mechanism
- EXPERIMENTAL: Possible country restrictions on TLDs are shown to customer on order page
- REGISTRAR PLATFORM: Customers using our Registrar Platform service can set price overrides for the TLDs they are accredited for.
- Two custom WHMCS API endpoints: `NordNameDomainCheck` for domain availability lookups and `NordNameGetTLD` for fetching TLD information

# Installation

1. Copy the files and folders within the repository to a folder modules/registrars/nordname within your WHMCS installation.
2. Copy the files from the `modules/registrars/nordname/lang/overrides` folder into the `lang/overrides` folder. If the `modules/registrars/nordname/lang/overrides` folder does not exist first, create it. If you already have language override files in that location, merge the files manually.
3. Go to Setup->Products/Services->Domain Registrars menu within your WHMCS admin area. Configure your API Key to the module settings, check any optional settings (descriptions of them are below), activate the registrar module and you are done!


## (optional) Install custom WHMCS API endpoints

If you wish to use our custom API Endpoints `NordNameDomainCheck` and `NordNameGetTLD`, copy all files from the `modules/registrars/nordname/api` folder into `includes/api` folder.


# Update from v1.2 to v3.0

To update the module version, do following steps:

1. Extract all files from this Git repository into the `modules/registrars/nordname` folder within your WHMCS installation. Override all existing files.
2. Go to Setup->Products/Services->Domain Registrars menu within your WHMCS admin area. Configure any new features, if you like. SAVE THE CONFIGURATION EVEN IF YOU MADE NO CHANGES. This is needed for the new Hooks to become effective.
3. Copy the files from the `modules/registrars/nordname/lang/overrides` folder into the `lang/overrides` folder. If the `modules/registrars/nordname/lang/overrides` folder does not exist first, create it. If you already have language override files in that location, merge the files manually.
4. (optional) If you wish to use our custom API Endpoints `NordNameDomainCheck` and `NordNameGetTLD`, copy all files from the `modules/registrars/nordname/api` folder into `includes/api` folder.


