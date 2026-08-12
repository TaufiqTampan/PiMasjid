<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;

    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Transaction::query()
            ->with('verifiedBy')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Tipe',
            'Kategori',
            'Keterangan',
            'Jumlah (Rp)',
            'Diverifikasi Oleh',
            'Dibuat Pada',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->date,
            $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            $transaction->category,
            $transaction->description,
            $transaction->amount,
            $transaction->verifiedBy->name ?? '-',
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
