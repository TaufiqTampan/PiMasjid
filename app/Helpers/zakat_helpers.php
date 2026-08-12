<?php

if (! function_exists('calculate_zakat_fitrah')) {
    /**
     * Calculate Zakat Fitrah amount
     *
     * @param  float  $ricePricePerLiter  Price of rice per liter
     * @param  int  $personCount  Number of persons
     * @return float Calculated zakat amount
     */
    function calculate_zakat_fitrah(float $ricePricePerLiter, int $personCount = 1): float
    {
        return 3.5 * $ricePricePerLiter * $personCount;
    }
}

if (! function_exists('calculate_zakat_mal')) {
    /**
     * Calculate Zakat Mal (2.5% of wealth if above nisab)
     *
     * @param  float  $harta  Total wealth
     * @param  float  $hutang  Total debt
     * @param  float  $goldPricePerGram  Current gold price per gram
     * @return array ['amount' => float, 'nisab' => float, 'is_above_nisab' => bool]
     */
    function calculate_zakat_mal(float $harta, float $hutang, float $goldPricePerGram): array
    {
        $nisab = 85 * $goldPricePerGram;
        $nettWealth = $harta - $hutang;
        $isAboveNisab = $nettWealth >= $nisab;
        $amount = $isAboveNisab ? $nettWealth * 0.025 : 0;

        return [
            'amount' => $amount,
            'nisab' => $nisab,
            'nett_wealth' => $nettWealth,
            'is_above_nisab' => $isAboveNisab,
        ];
    }
}

if (! function_exists('calculate_zakat_profesi')) {
    /**
     * Calculate Zakat Profesi (2.5% of income)
     *
     * @param  float  $penghasilan  Monthly income
     * @return float Calculated zakat amount
     */
    function calculate_zakat_profesi(float $penghasilan): float
    {
        return $penghasilan * 0.025;
    }
}

if (! function_exists('get_asnaf_categories')) {
    /**
     * Get list of 8 Asnaf categories
     *
     * @return array<string, string>
     */
    function get_asnaf_categories(): array
    {
        return [
            'fakir' => 'Fakir (Tidak punya harta/penghasilan)',
            'miskin' => 'Miskin (Penghasilan kurang)',
            'amil' => 'Amil (Pengelola zakat)',
            'muallaf' => 'Muallaf',
            'riqab' => 'Riqab (Memerdekakan budak)',
            'gharim' => 'Gharim (Berhutang)',
            'sabilillah' => 'Sabilillah (Jihad fi sabilillah)',
            'ibnu_sabil' => 'Ibnu Sabil (Musafir)',
        ];
    }
}

if (! function_exists('format_rupiah')) {
    /**
     * Format amount to Rupiah currency
     *
     * @param  float|int  $amount
     */
    function format_rupiah($amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}

if (! function_exists('validate_qurban_share')) {
    /**
     * Validate qurban share count based on animal type
     *
     * @param  string  $animalType  Type of animal
     * @param  int  $shareCount  Number of shares
     * @return array ['valid' => bool, 'message' => string|null]
     */
    function validate_qurban_share(string $animalType, int $shareCount): array
    {
        $smallAnimals = ['kambing', 'domba'];
        $largeAnimals = ['sapi', 'kerbau', 'unta'];

        if (in_array($animalType, $smallAnimals) && $shareCount > 1) {
            return [
                'valid' => false,
                'message' => 'Kambing/Domba tidak bisa dipatungankan (maksimal 1 orang).',
            ];
        }

        if (in_array($animalType, $largeAnimals) && $shareCount > 7) {
            return [
                'valid' => false,
                'message' => 'Sapi/Kerbau/Unta maksimal 7 orang.',
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }
}
