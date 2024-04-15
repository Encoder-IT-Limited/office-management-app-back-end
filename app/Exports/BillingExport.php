<?php

namespace App\Exports;

use App\Models\BillableTime;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillingExport implements FromCollection, WithHeadings, WithStyles
{
    use Exportable;

    protected $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $data = BillableTime::with('user', 'project', 'task');
        if ($this->ids && count($this->ids) > 0) {
            $data->whereIn('id', $this->ids);
        }
        return $data->get();
    }

    public function headings(): array
    {
        return [
            '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ]
            ],
        ];
    }
}
