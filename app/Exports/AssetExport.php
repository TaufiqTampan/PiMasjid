<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Asset::latest()->get();
    }

    public function headings(): array
    {
        return [
            'Nama Aset',
            'Kondisi',
            'Jumlah',
            'Tanggal Beli',
            'Harga Beli (Rp)',
            'Catatan',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->name,
            $this->translateCondition($asset->condition),
            $asset->quantity,
            $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-',
            $asset->purchase_price ? number_format($asset->purchase_price, 0, ',', '.') : '-',
            $asset->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function translateCondition($condition)
    {
        return match ($condition) {
            'good' => 'Baik',
            'damaged' => 'Rusak',
            'lost' => 'Hilang',
            default => $condition,
        };
    }
}
