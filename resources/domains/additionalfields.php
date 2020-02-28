<?php

$registrant_type = [
    'Name' => 'registrant_type',
    'DisplayName' => 'Registrant Type',
    'LangVar' => 'nordname_registrant_type',
    'Type' => 'dropdown',
    'Options' => implode(',', [
        '0|' . \Lang::trans('Private Person'),
        '1|' . \Lang::trans('Company'),
        '2|' . \Lang::trans('Corporation'),
        '3|' . \Lang::trans('Institution'),
        '4|' . \Lang::trans('Political Party'),
        '5|' . \Lang::trans('Township'),
        '6|' . \Lang::trans('Government'),
        '7|' . \Lang::trans('Public Community'),
    ]),
    "Required" => true
];

// .SE
$additionaldomainfields['.se'][] = $registrant_type;
$additionaldomainfields['.se'][] = [
    'Name' => 'idNumber',
    'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="Only for private individuals: Social Security Number or passport number; Companies, fill Register Number instead">what\'s this?</sup>',
    'LangVar' => 'nordname_se_idnumber',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.se'][] = [
    'Name' => 'registerNumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.se'][] = [
    'Name' => 'vatNumber',
    'DisplayName' => 'VAT Number <sup style="cursor:help;" title="Companies based in an EU country should also fill in their VAT number.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];

$additionaldomainfields['.nu'] = $additionaldomainfields['.se'];

// .FI
$additionaldomainfields['.fi'][] = $registrant_type;
$additionaldomainfields['.fi'][] = [
    'Name' => 'idNumber',
    'LangVar' => 'nordname_fi_idnumber',
    'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For Finnish Residents: Social Security Number; For residents of other countries, please fill your birthdate instead.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.fi'][] = [
    'Name' => 'registerNumber',
    'LangVar' => 'nordname_fi_registernumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.fi'][] = [
    "Name" => "birthdate",
    "DisplayName" => 'Birth Date <sup style="cursor:help;" title="Required for private persons not living in Finland">what\'s this?</sup>',
    'LangVar' => 'nordname_fi_birthdate',
    "Type" => "text",
    "Size" => "10",
    "Default" => "1900-01-01",
    "Required" => false
];

// .AX
$additionaldomainfields['.ax'][] = $registrant_type;
$additionaldomainfields['.ax'][] = [
    'Name' => 'idNumber',
    'LangVar' => 'nordname_ax_idnumber',
    'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For private individuals: Social Security Number or passport number.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.ax'][] = [
    'Name' => 'registerNumber',
    'LangVar' => 'nordname_fi_registernumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];

// .EE / .NO
$additionaldomainfields['.ee'][] = $registrant_type;
$additionaldomainfields['.ee'][] = [
    'Name' => 'idNumber',
    'LangVar' => 'nordname_ee_idnumber',
    'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For private individuals: Social Security Number or passport number; Companies, fill Register Number instead.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.ee'][] = [
    'Name' => 'registerNumber',
    'LangVar' => 'nordname_fi_registernumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.no'] = $additionaldomainfields['.ee'];

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
$additionaldomainfields['.hu'][] = $registrant_type;
$additionaldomainfields['.hu'][] = [
    'Name' => 'idNumber',
    'LangVar' => 'nordname_hu_idnumber',
    'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="Only for private individuals: Social Security Number or passport number; Companies, fill VAT Number instead">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
$additionaldomainfields['.hu'][] = [
    'Name' => 'vatNumber',
    'LangVar' => 'nordname_hu_vatnumber',
    'DisplayName' => 'VAT Number <sup style="cursor:help;" title="Only for companies: provide your European VAT number.">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];

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


// .LT
$additionaldomainfields['.lt'][] = $registrant_type;
$additionaldomainfields['.lt'][] = [
    'Name' => 'registerNumber',
    'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>',
    'Type' => 'text',
    'Size' => '20',
    'Required' => false
];
