<?php

namespace App\Services;

class TaxCalculatorService
{
    public const PPH_RATE = 0.025; // PPh Final 2,5%

    public const BPHTB_RATE = 0.05;  // BPHTB 5%

    /**
     * Hitung PPh Final Penjual
     */
    public function calculatePph(float $propertyPrice): float
    {
        return $propertyPrice * self::PPH_RATE;
    }

    /**
     * Hitung BPHTB Pembeli
     * Catatan: Dalam praktiknya, BPHTB dikurangi Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP).
     * Jika MVP ini mengabaikan NPOPTKP, kita set default $npoptkp = 0.
     */
    public function calculateBphtb(float $propertyPrice, float $npoptkp = 0): float
    {
        $taxableValue = max(0, $propertyPrice - $npoptkp);

        return $taxableValue * self::BPHTB_RATE;
    }
}
