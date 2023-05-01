<?php

namespace TinyFramework\Validation\Rule;

/**
 * ISO-3166-1 Number
 * @link https://de.wikipedia.org/wiki/ISO-3166-1-Kodierliste
 * document.querySelectorAll('.jquery-tablesorter tbody tr td:nth-child(4)').forEach(e => console.log(e.innerHTML));
 */
class CountryCodeNumberRule extends RuleAwesome
{
    private array $countryCodes = [
        '004',
        '818',
        '248',
        '008',
        '012',
        '016',
        '850',
        '020',
        '024',
        '660',
        '010',
        '028',
        '226',
        '032',
        '051',
        '533',
        '031',
        '231',
        '036',
        '044',
        '048',
        '050',
        '052',
        '112',
        '056',
        '084',
        '204',
        '060',
        '064',
        '068',
        '535',
        '070',
        '072',
        '074',
        '076',
        '092',
        '086',
        '096',
        '100',
        '854',
        '104',
        '108',
        '152',
        '156',
        '184',
        '188',
        '531',
        '208',
        '278',
        '276',
        '212',
        '214',
        '262',
        '218',
        '384',
        '222',
        '232',
        '233',
        '748',
        '238',
        '234',
        '242',
        '246',
        '250',
        '249',
        '254',
        '258',
        '260',
        '266',
        '270',
        '268',
        '288',
        '292',
        '308',
        '300',
        '304',
        '312',
        '316',
        '320',
        '831',
        '324',
        '624',
        '328',
        '332',
        '334',
        '340',
        '344',
        '356',
        '360',
        '833',
        '368',
        '364',
        '372',
        '352',
        '376',
        '380',
        '388',
        '392',
        '887',
        '832',
        '400',
        '891',
        '136',
        '116',
        '120',
        '124',
        '132',
        '398',
        '634',
        '404',
        '417',
        '296',
        '166',
        '170',
        '174',
        '180',
        '178',
        '408',
        '410',
        '191',
        '192',
        '414',
        '418',
        '426',
        '428',
        '422',
        '430',
        '434',
        '438',
        '440',
        '442',
        '446',
        '450',
        '454',
        '458',
        '462',
        '466',
        '470',
        '504',
        '584',
        '474',
        '478',
        '480',
        '175',
        '484',
        '583',
        '498',
        '492',
        '496',
        '499',
        '500',
        '508',
        '104',
        '516',
        '520',
        '524',
        '540',
        '554',
        '536',
        '558',
        '528',
        '530',
        '562',
        '566',
        '570',
        '580',
        '807',
        '574',
        '578',
        '512',
        '040',
        '626',
        '586',
        '275',
        '585',
        '591',
        '598',
        '600',
        '604',
        '608',
        '612',
        '616',
        '620',
        '630',
        '638',
        '646',
        '642',
        '643',
        '090',
        '652',
        '663',
        '894',
        '882',
        '674',
        '678',
        '682',
        '752',
        '756',
        '686',
        '688',
        '891',
        '690',
        '694',
        '716',
        '702',
        '534',
        '703',
        '705',
        '706',
        '810',
        '724',
        '144',
        '654',
        '659',
        '662',
        '666',
        '670',
        '710',
        '729',
        '239',
        '728',
        '740',
        '744',
        '760',
        '762',
        '158',
        '834',
        '764',
        '768',
        '772',
        '776',
        '780',
        '148',
        '203',
        '200',
        '788',
        '792',
        '795',
        '796',
        '798',
        '800',
        '804',
        '348',
        '581',
        '858',
        '860',
        '548',
        '336',
        '862',
        '784',
        '840',
        '826',
        '704',
        '876',
        '162',
        '732',
        '180',
        '140',
        '196',
    ];

    public function getName(): string
    {
        return 'countrycodenumber';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $countryCode = $attributes[$name] ?? null;
        $countryCode = $countryCode ? str_pad((string)$countryCode, 3, '0') : null;
        if (in_array($countryCode, $this->countryCodes, true)) {
            return null;
        }

        return [$this->translator->trans('validation.countrycodenumber', ['attribute' => $this->getTransName($name)])];
    }
}
