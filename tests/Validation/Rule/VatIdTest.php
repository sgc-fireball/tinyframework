<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\Ipv4Rule;
use TinyFramework\Validation\Rule\VatIdRule;
use TinyFramework\Validation\ValidationException;

class VatIdTest extends ValidationTestCase
{
    public function vatIdProvider(): array
    {
        return [
            ['vatid:at', 'ATU12345678', true],
            ['vatid:be', 'BE1234567890', true],
            ['vatid:bg', 'BG1234567890', true],
            ['vatid:bg', 'BG123456789', true],
            ['vatid:hr', 'HR12345678901', true],
            ['vatid:cy', 'CY123456789', true],
            ['vatid:cz', 'CZ1234567890', true],
            ['vatid:cz', 'CZ123456789', true],
            ['vatid:cz', 'CZ12345678', true],
            ['vatid:dk', 'DK12345678', true],
            ['vatid:ee', 'EE123456789', true],
            ['vatid:fi', 'FI12345678', true],
            ['vatid:fr', 'FRAA123456789', true],
            ['vatid:de', 'DE123456789', true],
            ['vatid:el', 'EL123456789', true],
            ['vatid:hu', 'HU12345678', true],
            ['vatid:ie', 'IE1234567AW', true],
            ['vatid:it', 'IT12345678910', true],
            ['vatid:lv', 'LV12345678901', true],
            ['vatid:lt', 'LT123456789012', true],
            ['vatid:lt', 'LT12345678901', true],
            ['vatid:lt', 'LT1234567890', true],
            ['vatid:lt', 'LT123456789', true],
            ['vatid:lu', 'LU12345678', true],
            ['vatid:mt', 'MT12345678', true],
            ['vatid:nl', 'NL123456789B10', true],
            ['vatid:pl', 'PL1234567890', true],
            ['vatid:pl', 'PL123-456-78-90', true],
            ['vatid:pl', 'PL123-45-67-890', true],
            ['vatid:pt', 'PT123456789', true],
            ['vatid:ro', 'RO1234567890', true],
            ['vatid:sk', 'SK1234567890', true],
            ['vatid:si', 'SI12345678', true],
            ['vatid:es', 'ESA1234567A', true],
            ['vatid:se', 'SE123456789012', true],
            ['vatid:al', 'AL123456789A', true],
            ['vatid:mk', 'MK1234567890123', true],
            ['vatid:au', 'AU12345678901', true],
            ['vatid:by', 'УНП 123456789', true],
            ['vatid:ca', 'CA123456789', true],
            ['vatid:is', 'IS123456', true],
            ['vatid:in', 'IN123456789012345', true],
            ['vatid:id', 'ID123456789012345', true],
            ['vatid:id', 'ID12.345.678.9-012.345', true],
            ['vatid:il', 'IL123456789', true],
            ['vatid:kz', 'KZ123456789', true],
            ['vatid:nz', 'NZ123456789', true],
            ['vatid:ng', 'NG123456789012', true],
            ['vatid:no', 'NO123456789MVA', true],
            ['vatid:ph', 'PH123456789012', true],
            ['vatid:ru', 'RU1234567890', true],
            ['vatid:ru', 'RU123456789012', true],
            ['vatid:sm', 'SM12345', true],
            ['vatid:sa', 'SA123456789012345', true],
            ['vatid:rs', 'RS123456789', true],
            ['vatid:ch', 'CHE-116.281.710 MWST', true],
            ['vatid:ch', 'CHE-116.281.710 IVA', true],
            ['vatid:ch', 'CHE-116.281.710 TVA', true],
            ['vatid:ch', 'CHE 116 281 710 IVA', true],
            ['vatid:ch', 'CHE-191.398.369MWST', true],
            ['vatid:ch', 'CHE-116281710 MWST', true],
            ['vatid:ch', 'CHE-116281710MWST', true],
            ['vatid:ch', 'CHE105854263MWST', true],
            ['vatid:ch', 'CHE-116.285.524', true],
            ['vatid:ch', 'CHE116281710', true],
            ['vatid:tr', 'TR1234567890', true],
            ['vatid:ua', 'UA123456789012', true],
            ['vatid:gb', 'GB999 9999 00', true],
            ['vatid:gb', 'GB999 9999 96', true],
            ['vatid:gb', 'GB999999999 999', true],
            ['vatid:gb', 'GBGD000', true],
            ['vatid:gb', 'GBGD499', true],
            ['vatid:gb', 'GBHA500', true],
            ['vatid:gb', 'GBHA999', true],
            ['vatid:uz', 'UZ123456789', true],
            ['vatid:ar', 'AR12345678901', true],
            ['vatid:bo', 'BO1234567', true],
            ['vatid:br', 'BR12.345.678/9012-34', true],
            ['vatid:br', 'BR123.456.789-01', true],
            ['vatid:cl', 'CL12345678-9', true],
            ['vatid:co', 'CO1234567890', true],
            ['vatid:cr', 'CR123456789012', true],
            ['vatid:cr', 'CR123456789', true],
            ['vatid:ec', 'EC1234567890123', true],
            ['vatid:sv', 'SV1234-567890-123-1', true],
            ['vatid:gt', 'GT1234567-8', true],
            ['vatid:mx', 'MXABCD123456EFG', true],
            ['vatid:mx', 'MXABC123456DEF', true],
            ['vatid:ni', 'NI123-456789-0123A', true],
            ['vatid:py', 'PY12345678-9', true],
            ['vatid:py', 'PY123456-7', true],
            ['vatid:pe', 'PE12345678901', true],
            ['vatid:do', 'DO12345678901', true],
            ['vatid:do', 'DO123-4567890-1', true],
            ['vatid:do', 'DO123456789', true],
            ['vatid:do', 'DO1-23-45678-9', true],
            ['vatid:uy', 'UY123456789012', true],
            ['vatid:ve', 'VEJ-123456789', true],
            ['vatid:ve', 'VEJ-12345678-9', true],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider vatIdProvider
     */
    public function testIp(string $rule, mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new VatIdRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => $rule]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
