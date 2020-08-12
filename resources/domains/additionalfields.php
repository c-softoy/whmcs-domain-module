<?php

/**
 * NordName Domain name additional fields resource file
 * 
 * 
 */

function nordname_registrant_types() {
    return [
        \Lang::trans('Private Person'),
        \Lang::trans('Company'),
        \Lang::trans('Corporation'),
        \Lang::trans('Institution'),
        \Lang::trans('Political Party'),
        \Lang::trans('Township'),
        \Lang::trans('Government'),
        \Lang::trans('Public Community')
    ];
}

// This function returns the descriptor for a certain field.
function nordname_additional_fields_bank($field, $tld=null) {
    // In the field bank we list all available extra fields.
    $bank = array(
        "registrant_type" => array(
            'Name' => 'registrant_type',
            'DisplayName' => 'Registrant Type',
            'LangVar' => 'nordname_registrant_type',
            'Type' => 'dropdown',
            'Options' => implode(',', nordname_registrant_types())
        ),
        "register_number" => array(
            'Name' => 'register_number',
            'DisplayName' => \Lang::trans('Business customers: Company registration number'),
            'LangVar' => 'nordname_register_number',
            'Type' => 'text',
            'Size' => '20',
            'Required' => false
        ),
        "id_number" => array(
            'Name' => 'id_number',
            'DisplayName' => 'ID Number',
            'Description' => \Lang::trans('Private persons: ID card or passport number'),
            'LangVar' => 'nordname_id_number',
            'Type' => 'text',
            'Size' => '20',
            'Required' => false
        ),
        "birth_date" => array(
            "Name" => "birth_date",
            "DisplayName" => 'Birth Date',
            'Description' => \Lang::trans('Birth date in format YYYY-MM-DD'),
            'LangVar' => 'nordname_birthdate',
            "Type" => "text",
            "Size" => "10",
            "Default" => "1999-01-01",
            "Required" => false
        ),
        "vat_number" => array(
            "Name" => "vat_number",
            "DisplayName" => 'VAT Number',
            'Description' => \Lang::trans('EU VAT Number. <a href="https://ec.europa.eu/taxation_customs/vies/">Check validity</a>'),
            'LangVar' => 'nordname_vat_number',
            "Type" => "text",
            "Size" => "10",
            "Required" => false
        ),
        "country_of_citizenship" => array(
            "Name" => "country_of_citizenship",
            "DisplayName" => 'Country of Citizenship',
            'LangVar' => 'nordname_country_of_citizenship',
            "Type" => "dropdown",
            "Options" => "{CountryCodeMap}"
        ),
    );
    // In the TLD descriptor bank we can override the name and descriptor to better match single TLDs.
    $tld_descriptors = array(
        "no" => array(
            "id_number" => array(
                "DisplayName" => "PID Number",
                "Description" => \Lang::trans('Private persons: your Personal ID generated at <a href="https://pid.norid.no/personid/lookup">NORID\'s PID Automat</a>.'),
                "LangVar" => 'nordname_no_id_number'
            ),
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
// .SE
$additionaldomainfields['.se'][] = nordname_additional_fields_bank("registrant_type", "se");
$additionaldomainfields['.se'][] = nordname_additional_fields_bank("id_number", "se");
$additionaldomainfields['.se'][] = nordname_additional_fields_bank("register_number", "se");
$additionaldomainfields['.se'][] = nordname_additional_fields_bank("vat_number", "se");
$additionaldomainfields['.nu'] = $additionaldomainfields['.se'];

// .FI
$additionaldomainfields['.fi'][] = nordname_additional_fields_bank("registrant_type", "fi");
$additionaldomainfields['.fi'][] = nordname_additional_fields_bank("id_number", "fi");
$additionaldomainfields['.fi'][] = nordname_additional_fields_bank("register_number", "fi");
$additionaldomainfields['.fi'][] = nordname_additional_fields_bank("birth_date", "fi");

// .AX
$additionaldomainfields['.ax'][] = nordname_additional_fields_bank("registrant_type", "ax");
$additionaldomainfields['.ax'][] = nordname_additional_fields_bank("id_number", "ax");
$additionaldomainfields['.ax'][] = nordname_additional_fields_bank("register_number", "ax");

//
// .EE / .NO
$additionaldomainfields['.ee'][] = nordname_additional_fields_bank("registrant_type", "ee");
$additionaldomainfields['.ee'][] = nordname_additional_fields_bank("id_number", "ee");
$additionaldomainfields['.ee'][] = nordname_additional_fields_bank("register_number", "ee");
$additionaldomainfields['.no'][] = nordname_additional_fields_bank("registrant_type", "no");
$additionaldomainfields['.no'][] = nordname_additional_fields_bank("id_number", "no");
$additionaldomainfields['.no'][] = nordname_additional_fields_bank("register_number", "no");


// .ES
$additionaldomainfields['.es'][] = [
    'Name' => 'es_tipo',
    'LangVar' => 'nordname_owner_type',
    'DisplayName' => 'Owner Type',
    'Type' => 'dropdown',
    'Options' => implode(',', [
        '0|' . \Lang::trans('Foreign'),
        '1|' . \Lang::trans('Spanish Citizen or Company'),
        '3|' . \Lang::trans('Foreigner living in Spain'),
    ]),
    "Required" => true
];
$additionaldomainfields['.es'][] = [
    'Name' => 'es_other',
    'LangVar' => 'nordname_es_idnumber',
    'DisplayName' => 'Personal Identification Number or Company Number <sup style="cursor:help;" title="For Foreign domain holders.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.es'][] = [
    'Name' => 'es_nif',
    'LangVar' => 'nordname_es_nif',
    'DisplayName' => 'NIF, fiscal identification number in Spain <sup style="cursor:help;" title="For Spanish citizens only.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.es'][] = [
    'Name' => 'es_nie',
    'LangVar' => 'nordname_es_nie',
    'DisplayName' => 'NIE, foreign identification number in Spain <sup style="cursor:help;" title="For foreigners living in Spain only.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];

// .UK
$additionaldomainfields['.uk'][] = [
    'Name' => 'registerNumber',
    'LangVar' => 'nordname_uk_registernumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for businesses located in the UK">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.co.uk'] = $additionaldomainfields['.uk'];

// .HU
$additionaldomainfields['.hu'][] = nordname_additional_fields_bank("registrant_type", "hu");
$additionaldomainfields['.hu'][] = nordname_additional_fields_bank("id_number", "hu");
$additionaldomainfields['.hu'][] = nordname_additional_fields_bank("vat_number", "hu");

// .US
$additionaldomainfields['.us'][] = [
    'Name' => 'us_nc',
    'DisplayName' => '.US Nexus Category',
    'LangVar' => 'nordname_us_nexus',
    'Type' => 'dropdown',
    'Options' => implode(',', [
        'C11|' . \Lang::trans('US Citizen'),
        'C12|' . \Lang::trans('US Permanent Resident'),
        'C21|' . \Lang::trans('Incorporated or organized in US'),
        'C31|' . \Lang::trans('Foreign entity doing business in US'),
        'C32|' . \Lang::trans('Foreign entity with office in US'),
    ]),
    "Required" => true
];
$additionaldomainfields['.us'][] = [
    'Name' => 'us_ap',
    'DisplayName' => '.US Application Purpose',
    'LangVar' => 'nordname_us_purpose',
    'Options' => implode(',', [
        'P1|' . \Lang::trans('Business for Profit'),
        'P2|' . \Lang::trans('Non-Profit'),
        'P3|' . \Lang::trans('Personal'),
        'P4|' . \Lang::trans('Educational'),
        'P5|' . \Lang::trans('Governmental'),
    ]),
    'Type' => 'dropdown',
    "Required" => true
];

// .HR
$additionaldomainfields['.hr'][] = [
    'Name' => 'hr_oib',
    'LangVar' => 'nordname_hr_oib',
    'DisplayName' => 'OIB Number <sup style="cursor:help;" title="Only required for domain holders living in HR">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
