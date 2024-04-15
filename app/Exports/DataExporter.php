<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class DataExporter implements FromCollection, WithHeadings
{
    use Exportable;

    private $ids;
    private $model;
    private $exortableData;
    private $columns;
    private $head;

    private $writerType = Excel::XLSX;

    public function __construct($ids = null, $model = null, $columns = [], $head = [], $exortableData = null)
    {
        $this->ids = $ids;
        $this->model = $model;
        $this->columns = $columns;
        $this->head = $head;
        $this->exortableData = $exortableData;
    }

    public function collection(): \Illuminate\Support\Collection
    {
//        $model = '\App\Models\\' . $this->model;
        if ($this->exortableData) {
            $modelDatas = $this->exortableData;
        } else {
            $model = $this->model;
            if ($this->ids) {
                $modelDatas = $model::whereIn('id', $this->ids)->get();
            } else {
                $modelDatas = $model::all();
            }
        }

        $data = array();
        $cols = $this->columns;
        foreach ($modelDatas as $modelData) {
            $info = array();
            if (count($cols)) {
                foreach ($cols as $col) {
                    if ($col === 'active') {
                        $info['status'] = $modelData[$col] ? 'Active' : 'Disabled';
                    } else {
//                        $output = str_replace(['_', '.'], ' ', $col);
                        $output = $col;
                        $relations = explode('.', $col);
                        if (count($relations) > 1) {
                            $r = $modelData;
                            foreach ($relations as $relation) {
                                if (!empty($r) && $r->exists()) {
                                    $r = $r->$relation;
                                }
                            }
                            $info[$output] = $r ?? '';
                        } else {
                            $info[$output] = $modelData[$col];
                        }
                    }
                }
            }
            $data[] = $info;
        }
        return collect($data);
    }

    public function headings(): array
    {
        return $this->head;
    }
}
