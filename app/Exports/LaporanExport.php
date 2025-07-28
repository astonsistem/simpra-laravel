<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class LaporanExport implements FromView, WithEvents, WithColumnFormatting
{
    protected $laporanName, $laporanTitle, $laporanData;

    public function __construct($laporanName, $laporanTitle, $laporanData)
    {
        $this->laporanName  = $laporanName;
        $this->laporanTitle = $laporanTitle;
        $this->laporanData  = $laporanData;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setSize(9);
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A:Z' => '0'
        ];
    }

    public function view(): View
    {
        return view($this->laporanName, [
            'laporanTitle' => $this->laporanTitle,
            'laporanData'  => $this->laporanData,
        ]);
    }

}
