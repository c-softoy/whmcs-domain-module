<?php

/**
 * NordName Domain name additional fields resource file
 * 
 * 
 */

function nordname_registrant_types() {
    return [
        'Private Person|' . \Lang::trans('Private Person'),
        'Company|' . \Lang::trans('Company'),
        'Association|' . \Lang::trans('Association'),
        'Institution|' . \Lang::trans('Institution'),
        'Political Party|' . \Lang::trans('Political Party'),
        'Township|' . \Lang::trans('Township'),
        'Government|' . \Lang::trans('Government'),
        'Public Community|' . \Lang::trans('Public Community')
    ];
}

// This function returns the descriptor for a certain field.
function nordname_additional_fields_bank($field, $tld=null) {
    // In the field bank we list all available extra fields.
    $bank = array(
        "registrant_type" => array(
            'Name' => 'registrant_type',
            'DisplayName' => \Lang::trans('Registrant Type'),
            'LangVar' => 'nordname_registrant_type',
            'Type' => 'dropdown',
            'Options' => implode(',', nordname_registrant_types()),
            'IsStandard' => true
        ),
        "register_number" => array(
            'Name' => 'register_number',
            'DisplayName' => \Lang::trans('Business registration number'),
            'Description' => \Lang::trans('Business customers: Company registration number'),
            'LangVar' => 'nordname_register_number',
            'Type' => 'text',
            'Size' => '20',
            'Required' => false,
            'IsStandard' => true
        ),
        "id_number" => array(
            'Name' => 'id_number',
            'DisplayName' => \Lang::trans('ID Number'),
            'Description' => \Lang::trans('Private persons: ID card or passport number'),
            'LangVar' => 'nordname_id_number',
            'Type' => 'text',
            'Size' => '20',
            'Required' => false,
            'IsStandard' => true
        ),
        "birth_date" => array(
            "Name" => "birth_date",
            "DisplayName" => \Lang::trans('Birth Date'),
            'Description' => \Lang::trans('Birth date in format YYYY-MM-DD'),
            'LangVar' => 'nordname_birthdate',
            "Type" => "text",
            "Size" => "10",
            "Default" => "1999-01-01",
            "Required" => false,
            'IsStandard' => true
        ),
        "vat_number" => array(
            "Name" => "vat_number",
            "DisplayName" => \Lang::trans('VAT Number'),
            'Description' => \Lang::trans('EU VAT Number. <a href="https://ec.europa.eu/taxation_customs/vies/">Check validity</a>'),
            'LangVar' => 'nordname_vat_number',
            "Type" => "text",
            "Size" => "10",
            "Required" => false,
            'IsStandard' => true
        ),
        "country_of_citizenship" => array(
            "Name" => "country_of_citizenship",
            "DisplayName" => \Lang::trans('Country of Citizenship'),
            'LangVar' => 'nordname_country_of_citizenship',
            "Type" => "dropdown",
            "Options" => "{CountryCodeMap}",
            'IsStandard' => true
        ),
        "NORID_PID" => array(
            'Name' => 'NORID_PID',
            'DisplayName' => 'NORID PID',
            'Description' => \Lang::trans('Personal ID number for .NO domain names. You can create one at https://pid.norid.no/'),
            'LangVar' => 'nordname_NORID_PID',
            'Type' => 'text',
            'Size' => '20',
            'Required' => false,
            'IsStandard' => false
        ),
        "US_APP_PURPOSE" => array(
            'Name' => 'US_APP_PURPOSE',
            'DisplayName' => 'US App Purpose',
            'Description' => \Lang::trans('.US domain application purpose'),
            'LangVar' => 'nordname_US_APP_PURPOSE',
            "Type" => "dropdown",
            "Options" => "P1|Business use for profit,P2|Non-profit business,P3|Personal use,P4|Educational purposes,P5|Government purposes",
            "Default" => "P1",
            'Required' => false,
            'IsStandard' => false
        ),
        "US_CATEGORY" => array(
            'Name' => 'US_CATEGORY',
            'DisplayName' => 'US Category',
            'Description' => \Lang::trans('Category of .US domain applicant'),
            'LangVar' => 'nordname_US_CATEGORY',
            "Type" => "dropdown",
            "Options" => "C11|C11 - United States Citizen,C12|C12 - Permanent Resident of the United States,C21|C21 - A U.S.-based organization formed within the United States of America,C31|C31 - A foreign entity or organization that has a bona fide presence in the United States of America,C32|C32 - An entity or Organisation that has an office or other facility in the United States",
            "Default" => "C11",
            'Required' => false,
            'IsStandard' => false
        ),
    );

    // In the TLD descriptor bank we can override the name and descriptor to better match single TLDs.
    $tld_descriptors = array(
        "no" => array(
            "register_number" => array(
                "DisplayName" => "Norwegian company number (ORGNR)",
                "Description" => \Lang::trans('Business customers: ORGNR from Norwegian Enhetsregisteret.'),
                "LangVar" => 'nordname_no_register_number'
            )
        ),
        "se" =>  array( // Also for .nu
            "id_number" => array(
                "DisplayName" => "ID Number",
                "Description" => \Lang::trans('Private persons: your national ID number / "personnummer" in format YYMMDD-XXXX.'),
                "LangVar" => 'nordname_se_id_number'
            ),
            "register_number" => array(
                "DisplayName" => "Company ID (ORGNR)",
                "Description" => \Lang::trans('Business customers: your company registration number / "organisationsnummer".'),
                "LangVar" => 'nordname_se_register_number'
            )
        ),
        "fi" => array(
            "birth_date" => array(
                "Description" => \Lang::trans('For private persons not resident in Finland: Birth date in format YYYY-MM-DD'),
                "LangVar" => 'nordname_fi_birth_date'
            )
        )
    );
    
    // Check if the field required exists in our bank. Otherwise return null.
    if (array_key_exists($field, $bank)) {
        $arr = $bank[$field];
        // Check if the TLD in question has a more detailed descriptor and update where available.
        if (!is_null($tld) && array_key_exists($tld, $tld_descriptors) && array_key_exists($field, $tld_descriptors[$tld])) {
            foreach ($tld_descriptors[$tld][$field] as $key => $value) {
                $arr[$key] = $value;
            }
        }
        return $arr;
    } else {
        return null;
    }
}