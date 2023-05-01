<?php

namespace TinyFramework\Validation\Rule;

/**
 * @link https://github.com/validatorjs/validator.js/blob/master/src/lib/isVAT.js
 */
class VatIdRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'vatid';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $vatId = $attributes[$name] ?? null;
        if (is_string($vatId) && mb_strlen($vatId) > 2) {
            $validCountries = count($parameters) ? $parameters : [mb_substr($vatId, 0, 2)];
            foreach ($validCountries as $country) {
                if ($this->testByCountry($vatId, $country)) {
                    return null;
                }
            }
        }

        return [$this->translator->trans('validation.vat', ['attribute' => $this->getTransName($name)])];
    }

    private function testByCountry(string $vatId, string $country): bool
    {
        $country = strtoupper($country);
        if (in_array($country, ['PT', 'CH'])) {
            return $this->{'check' . $country}($vatId);
        }

        $regex = match ($country) {
            'AT' => '/^(AT)U\d{8}$/',
            'BE' => '/^(BE)\d{10}$/',
            'BG' => '/^(BG)\d{9,10}$/',
            'HR' => '/^(HR)\d{11}$/',
            'CY' => '/^(CY)\w{9}$/',
            'CZ' => '/^(CZ)\d{8,10}$/',
            'DK' => '/^(DK)\d{8}$/',
            'EE' => '/^(EE)\d{9}$/',
            'FI' => '/^(FI)\d{8}$/',
            'FR' => '/^(FR)\w{2}\d{9}$/',
            'DE' => '/^(DE)\d{9}$/',
            'EL' => '/^(EL)\d{9}$/',
            'HU' => '/^(HU)\d{8}$/',
            'IE' => '/^(IE)\d{7}\w{1}(W)?$/',
            'IT' => '/^(IT)\d{11}$/',
            'LV' => '/^(LV)\d{11}$/',
            'LT' => '/^(LT)\d{9,12}$/',
            'LU' => '/^(LU)\d{8}$/',
            'MT' => '/^(MT)\d{8}$/',
            'NL' => '/^(NL)\d{9}B\d{2}$/',
            'PL' => '/^(PL)(\d{10}|(\d{3}-\d{3}-\d{2}-\d{2})|(\d{3}-\d{2}-\d{2}-\d{3}))$/',
            // PT
            'RO' => '/^(RO)\d{2,10}$/',
            'SK' => '/^(SK)\d{10}$/',
            'SI' => '/^(SI)\d{8}$/',
            'ES' => '/^(ES)\w\d{7}[A-Z]$/',
            'SE' => '/^(SE)\d{12}$/',

            /**
             * VAT numbers of non-EU countries
             */
            'AL' => '/^(AL)\w{9}[A-Z]$/',
            'MK' => '/^(MK)\d{13}$/',
            'AU' => '/^(AU)\d{11}$/',
            'BY' => '/^(УНП )\d{9}$/',
            'CA' => '/^(CA)\d{9}$/',
            'IS' => '/^(IS)\d{5,6}$/',
            'IN' => '/^(IN)\d{15}$/',
            'ID' => '/^(ID)(\d{15}|(\d{2}.\d{3}.\d{3}.\d{1}-\d{3}.\d{3}))$/',
            'IL' => '/^(IL)\d{9}$/',
            'KZ' => '/^(KZ)\d{9}$/',
            'NZ' => '/^(NZ)\d{9}$/',
            'NG' => '/^(NG)(\d{12}|(\d{8}-\d{4}))$/',
            'NO' => '/^(NO)\d{9}MVA$/',
            'PH' => '/^(PH)(\d{12}|\d{3} \d{3} \d{3} \d{3})$/',
            'RU' => '/^(RU)(\d{10}|\d{12})$/',
            'SM' => '/^(SM)\d{5}$/',
            'SA' => '/^(SA)\d{15}$/',
            'RS' => '/^(RS)\d{9}$/',
            // CH,
            'TR' => '/^(TR)\d{10}$/',
            'UA' => '/^(UA)\d{12}$/',
            'GB' => '/^GB((\d{3} \d{4} ([0-8][0-9]|9[0-6]))|(\d{9} \d{3})|(((GD[0-4])|(HA[5-9]))[0-9]{2}))$/',
            'UZ' => '/^(UZ)\d{9}$/',

            /**
             * VAT numbers of Latin American countries
             */
            'AR' => '/^(AR)\d{11}$/',
            'BO' => '/^(BO)\d{7}$/',
            'BR' => '/^(BR)((\d{2}.\d{3}.\d{3}\/\d{4}-\d{2})|(\d{3}.\d{3}.\d{3}-\d{2}))$/',
            'CL' => '/^(CL)\d{8}-\d{1}$/',
            'CO' => '/^(CO)\d{10}$/',
            'CR' => '/^(CR)\d{9,12}$/',
            'EC' => '/^(EC)\d{13}$/',
            'SV' => '/^(SV)\d{4}-\d{6}-\d{3}-\d{1}$/',
            'GT' => '/^(GT)\d{7}-\d{1}$/',
            'MX' => '/^(MX)\w{3,4}\d{6}\w{3}$/',
            'NI' => '/^(NI)\d{3}-\d{6}-\d{4}\w{1}$/',
            'PY' => '/^(PY)\d{6,8}-\d{1}$/',
            'PE' => '/^(PE)\d{11}$/',
            'DO' => '/^(DO)(\d{11}|(\d{3}-\d{7}-\d{1})|[1,4,5]{1}\d{8}|([1,4,5]{1})-\d{2}-\d{5}-\d{1})$/',
            'UY' => '/^(UY)\d{12}$/',
            'VE' => '/^(VE)[J,G,V,E]{1}-(\d{9}|(\d{8}-\d{1}))$/',
            default => null,
        };

        return $regex && (int)preg_match($regex, $vatId) >= 1;
    }

    /**
     * @internal
     */
    private function checkPT(string $vatId): bool
    {
        preg_match('/^(PT)(\d{9})$/', $vatId, $match);
        if (!$match) {
            return false;
        }
        $tin = $match[2];
        $digits = str_split($tin);
        $digits = array_slice($digits, 0, 8);
        $digits = array_map(fn ($i) => intval($i), $digits);
        $checksum = 11 - ($this->reverseMultiplyAndSum($digits, 9) % 11);
        if ($checksum > 9) {
            return intval($tin[8]) === 0;
        }
        return $checksum === intval($tin[8]);
    }

    /**
     * @internal
     */
    private function checkCH(string $vatId): bool
    {
        /**
         * @link https://www.ech.ch/de/ech/ech-0097/5.2.0
         */
        $hasValidCheckNumber = function (array $digits) {
            $lastDigit = array_pop($digits);
            $weights = [5, 4, 3, 2, 7, 6, 5, 4];
            $idx = 0;
            $reduce = array_reduce(
                $digits,
                function ($acc, $el) use ($weights, &$idx) {
                    return $acc + ($el * $weights[$idx++]);
                },
                0
            );
            $calculatedCheckNumber = (11 - ($reduce % 11)) % 11;
            return $lastDigit === $calculatedCheckNumber;
        };

        $numbers = preg_replace('/[^0-9]/', '', $vatId);
        $numbers = str_split($numbers);
        $numbers = array_map(fn ($num) => +$num, $numbers);

        /**
         * @link https://www.estv.admin.ch/estv/de/home/mehrwertsteuer/uid/mwst-uid-nummer.html
         */
        $regex = '/^(CHE[- ]?)(\d{9}|(\d{3}\.\d{3}\.\d{3})|(\d{3} \d{3} \d{3})) ?(TVA|MWST|IVA)?$/';
        return preg_match($regex, $vatId) >= 1 && $hasValidCheckNumber($numbers);
    }

    private function reverseMultiplyAndSum(array $digits, int $base): int
    {
        $total = 0;
        for ($i = 0; $i < count($digits); $i++) {
            $total += $digits[$i] * ($base - $i);
        }
        return $total;
    }
}
