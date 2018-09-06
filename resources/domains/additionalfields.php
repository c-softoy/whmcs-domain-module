<?php

$registrant_type = array('Name'	=> 'registrant_type', 'DisplayName' => 'Registrant Type', 'Type' => 'dropdown', 'Options' => 'Private Person,Company,Corporation,Institution,Political Party,Township,Government,Public Community', "Required" => true);

// .SE
$additionaldomainfields['.se'][] = $registrant_type;
$additionaldomainfields['.se'][] = array('Name' => 'idNumber', 'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="Only for private individuals: Social Security Number or passport number; Companies, fill Register Number instead">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.se'][] = array('Name' => 'registerNumber', 'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.se'][] = array('Name' => 'vatNumber', 'DisplayName' => 'VAT Number <sup style="cursor:help;" title="Companies based in an EU country should also fill in their VAT number.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);

$additionaldomainfields['.nu'] = $additionaldomainfields['.se'];

// .FI
$additionaldomainfields['.fi'][] = $registrant_type;
$additionaldomainfields['.fi'][] = array('Name' => 'idNumber', 'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For Finnish Residents: Social Security Number; For residents of other countries, please fill your birthdate instead.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.fi'][] = array('Name' => 'registerNumber', 'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.fi'][] = array("Name" => "birthdate", "DisplayName" => 'Birth Date <sup style="cursor:help;" title="Required for private persons not living in Finland">what\'s this?</sup>', "Type" => "text","Size" => "10","Default" => "1900-01-01","Required" => false);

// .AX
$additionaldomainfields['.ax'][] = $registrant_type;
$additionaldomainfields['.ax'][] = array('Name' => 'idNumber', 'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For private individuals: Social Security Number or passport number.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.ax'][] = array('Name' => 'registerNumber', 'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);

// .EE / .NO
$additionaldomainfields['.ee'][] = $registrant_type;
$additionaldomainfields['.ee'][] = array('Name' => 'idNumber', 'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="For private individuals: Social Security Number or passport number; Companies, fill Register Number instead.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.ee'][] = array('Name' => 'registerNumber', 'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for companies/organizations: Organization Registration Number">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.no'] = $additionaldomainfields['.ee'];

// .ES
$additionaldomainfields['.es'][] = array('Name'	=> 'es_tipo', 'DisplayName' => 'Owner Type', 'Type' => 'dropdown', 'Options' => 'Foreign,Spanish Citizen or Company,Foreigner living in Spain', "Required" => true);
$additionaldomainfields['.es'][] = array('Name' => 'es_other', 'DisplayName' => 'Personal Identification Number or Company Number <sup style="cursor:help;" title="For Foreign domain holders.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.es'][] = array('Name' => 'es_nif', 'DisplayName' => 'NIF, fiscal identification number in Spain <sup style="cursor:help;" title="For Spanish citizens only.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.es'][] = array('Name' => 'es_nie', 'DisplayName' => 'NIE, foreign identification number in Spain <sup style="cursor:help;" title="For foreigners living in Spain only.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);

// .UK
$additionaldomainfields['.uk'][] = array('Name' => 'registerNumber', 'DisplayName' => 'Register Number <sup style="cursor:help;" title="Only for businesses located in the UK">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.co.uk'] = $additionaldomainfields['.uk'];

// .HU
$additionaldomainfields['.hu'][] = $registrant_type;
$additionaldomainfields['.hu'][] = array('Name' => 'idNumber', 'DisplayName' => 'Social Security Number <sup style="cursor:help;" title="Only for private individuals: Social Security Number or passport number; Companies, fill VAT Number instead">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
$additionaldomainfields['.hu'][] = array('Name' => 'vatNumber', 'DisplayName' => 'VAT Number <sup style="cursor:help;" title="Only for companies: provide your European VAT number.">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);

// .US
$additionaldomainfields['.us'][] =  array('Name'	=> 'us_nc', 'DisplayName' => '.US Nexus Category', 'Type' => 'dropdown', 'Options' => 'US Citizen,US Permanent Resident,Incorporated or organized in US,Foreign entity doing business in US,Foreign entity with office in US', "Required" => true);
$additionaldomainfields['.us'][] =  array('Name'	=> 'us_ap', 'DisplayName' => '.US Application Purpose', 'Type' => 'dropdown', 'Options' => 'Business for Profit,Non-Profit,Personal,Educational,Governmental', "Required" => true);

// .HR
$additionaldomainfields['.hr'][] = array('Name' => 'hr_oib', 'DisplayName' => 'OIB Number <sup style="cursor:help;" title="Only required for domain holders living in HR">what\'s this?</sup>', 'Type' => 'text', 'Size' => '20', 'Required'	=> false);
