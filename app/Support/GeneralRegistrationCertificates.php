<?php

namespace App\Support;

class GeneralRegistrationCertificates
{
    public const ARAB_CERTIFICATES = [
        'Saudi Arabia' => 'Saudi Arabia (All Tracks / General / Paths)',
        'Kuwait' => 'Kuwait (Scientific / Literary)',
        'United Arab Emirates' => 'UAE (Elite / Advanced / General Tracks)',
        'Qatar' => 'Qatar National Secondary School Certificate',
        'Bahrain' => 'Bahrain Secondary School Certificate',
        'Oman' => 'Oman General Secondary Certificate',
        'Jordan' => 'Jordan - Tawjihi',
        'Syria' => 'Syria - Al-Shahada Al-Thanawiya',
        'Iraq' => 'Iraq - Sixth Preparatory Certificate',
        'Palestine' => 'Palestine - Tawjihi',
        'Lebanon' => 'Lebanon - Lebanese Baccalaureate',
        'Yemen' => 'Yemen Secondary School Certificate',
        'Libya' => 'Libya Secondary School Certificate',
        'Tunisia' => 'Tunisia - Baccalauréat',
        'Algeria' => 'Algeria - Baccalauréat',
        'Morocco' => 'Morocco - Baccalauréat',
        'Sudan' => 'Sudan Higher Secondary School Certificate',
        'Mauritania' => 'Mauritania - Baccalauréat',
        'Djibouti' => 'Djibouti - Baccalauréat',
        'Somalia' => 'Somalia Secondary School Leaving Certificate',
        'Comoros' => 'Comoros - Baccalauréat',
    ];

    public const INTERNATIONAL_CERTIFICATES = [
        'British System (IGCSE / GCSE / GCE A-Levels)',
        'American High School Diploma (+ SAT/ACT/EST)',
        'International Baccalaureate (IB Diploma)',
        'French Baccalauréat (General/International)',
        'German Abitur',
        'Italian Maturità',
        'Canadian High School Diploma (By Province)',
    ];

    public const ASIAN_CERTIFICATES = [
        'Turkey' => 'Turkey - Lise Diploması',
        'Malaysia' => 'Malaysia - SPM / STPM',
        'Indonesia' => 'Indonesia - Ijazah SMA',
        'Pakistan' => 'Pakistan - Higher Secondary School Certificate (HSSC)',
        'India' => 'India - CBSE / ICSE / State Board 12th Standard',
    ];

    public const INTERNATIONAL_COUNTRIES = [
        'United Kingdom', 'United States', 'France', 'Germany', 'Italy', 'Canada',
    ];

    public const ADDITIONAL_COUNTRIES = ['Egypt', 'Nigeria', 'Iran', 'Cyprus'];

    public const ARAB_CATEGORY = 'Arab High School Certificates';

    public const INTERNATIONAL_CATEGORY = 'International Systems';

    public const ASIAN_CATEGORY = 'Asian National Certificates';

    public static function countries(): array
    {
        return [
            ...array_keys(self::ARAB_CERTIFICATES),
            ...array_keys(self::ASIAN_CERTIFICATES),
            ...self::INTERNATIONAL_COUNTRIES,
            ...self::ADDITIONAL_COUNTRIES,
            'Other',
        ];
    }

    public static function categories(): array
    {
        return [self::ARAB_CATEGORY, self::INTERNATIONAL_CATEGORY, self::ASIAN_CATEGORY];
    }

    public static function certificatesForCategory(string $category): array
    {
        return match ($category) {
            self::ARAB_CATEGORY => array_values(self::ARAB_CERTIFICATES),
            self::INTERNATIONAL_CATEGORY => self::INTERNATIONAL_CERTIFICATES,
            self::ASIAN_CATEGORY => array_values(self::ASIAN_CERTIFICATES),
            default => [],
        };
    }
}
