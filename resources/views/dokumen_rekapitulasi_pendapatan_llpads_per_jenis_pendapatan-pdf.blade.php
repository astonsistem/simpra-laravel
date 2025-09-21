<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: 'sans-serif';
                line-height: 1.6;
            }
            hr {
                padding: 0px;
                margin: 0px;    
                opacity: 0.5;
            }
            .generate-info {
                font-style: italic; 
                font-size: 7px; 
            }
            .table-header {
                text-align: center; 
                vertical-align: middle; 
                font-weight: bold; 
                border: 1px solid black; 
                font-size: 13px; 
                word-wrap: break-word;
            }
            .table-data {
                text-align: center; 
                vertical-align: top; 
                border: 1px solid black; 
                font-size: 13px; 
                word-wrap: break-word;
                page-break-inside: avoid !important;
            }
            .text-left {
                text-align: left; 
                padding-left: 10px;
            }
            .text-right {
                text-align: right; 
                padding-right: 10px;
            }
        </style>
    </head>
    <body>
        <div style="position: fixed; left: 0px; top: -60px; right: 0px;">
            <p class="generate-info">Digenerate pada {{ \Carbon\Carbon::now()->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('H:i, l j F Y') }}</p>
            <hr>
        </div>
        <div style="text-align: center;">
            <img src="{{ public_path('header-pelaporan.png') }}" alt="Logo" class="img-fluid">
        </div>
        <hr style="border:none; border-top:1px dashed #000; margin:30px 0;">
        @php
            $title = mb_convert_case($laporanTitle, MB_CASE_TITLE, "UTF-8");
            $title = str_replace('Rsud', 'RSUD', $title);
        @endphp
        <h1 style="text-align: center; font-weight: bold; font-size: 15px; margin-bottom: 30px; margin-top: 0;">{!! $title !!}<br>No. {{ $laporanData[0]->nomor }}</h1>
        <table style="border-spacing: 0; margin: auto;">
            <thead>
                <tr>
                    <td class="table-header" style="width: 30px; padding-top: 10px; padding-bottom: 10px;">No</td>
                    <td class="table-header" style="width: 250px; padding-top: 10px; padding-bottom: 10px;">Uraian</td>
                    <td class="table-header" style="width: 150px; padding-top: 10px; padding-bottom: 10px;">Uang Muka</td>
                    <td class="table-header" style="width: 150px; padding-top: 10px; padding-bottom: 10px;">Pendapatan</td>
                    <td class="table-header" style="width: 150px; padding-top: 10px; padding-bottom: 10px;">Piutang</td>
                    <td class="table-header" style="width: 150px; padding-top: 10px; padding-bottom: 10px;">Total</td>
                </tr>
            </thead>
            @php
                $collectData = collect($laporanData);
            @endphp
            @foreach ($collectData as $row)
                <tr>
                    <td class="table-data">{{ $loop->iteration }}</td>
                    <td class="table-data text-left">{{ $row->uraian }}</td>
                    <td class="table-data text-right">{{ $row->pdd ? number_format($row->pdd, 2, ",", ".") : '-' }}</td>
                    <td class="table-data text-right">{{ $row->pendapatan ? number_format($row->pendapatan, 2, ",", ".") : '-' }}</td>
                    <td class="table-data text-right">{{ $row->piutang ? number_format($row->piutang, 2, ",", ".") : '-' }}</td>
                    <td class="table-data text-right">{{ $row->total ? number_format($row->total, 2, ",", ".") : '-' }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="table-header" colspan="2">Total</td>
                <td class="table-header text-right">{{ $collectData->sum('pdd') ? number_format($collectData->sum('pdd'), 2, ",", ".") : '-' }}</td>
                <td class="table-header text-right">{{ $collectData->sum('pendapatan') ? number_format($collectData->sum('pendapatan'), 2, ",", ".") : '-' }}</td>
                <td class="table-header text-right">{{ $collectData->sum('piutang') ? number_format($collectData->sum('piutang'), 2, ",", ".") : '-' }}</td>
                <td class="table-header text-right">{{ $collectData->sum('total') ? number_format($collectData->sum('total'), 2, ",", ".") : '-' }}</td>
            </tr>
        </table>
        <div style="font-size: 15px; width: 100%; margin-top: 10px; margin-left: 10px">
            <p style="float: left; width: 50%; text-align: left;">
                Mengetahui,<br>
                Kepala Bagian Keuangan
            </p>
            <p style="float: right; width: 50%; text-align: right; margin-top: 20px; margin-right: 20px;">
                Disusun oleh staf Sub Bag. Pendapatan :<br>
                <span style="display: inline-block; width: 100%; padding-right: 155px;">1. Finadini Rosida</span><br>
                <span style="display: inline-block; width: 100%; padding-right: 150px; padding-top: 30px;">2. Indah Novitasari</span>
            </p>
        </div>
        @php
            use Endroid\QrCode\Builder\Builder;
            use Endroid\QrCode\Writer\PngWriter;
            $qr = Builder::create()
                ->writer(new PngWriter())
                ->data($kabagInfo->nip .' - '. $laporanData[0]->nomor)
                ->size(200)
                ->build();
            $qrCode = base64_encode($qr->getString());
        @endphp
        <div style="font-size: 15px; margin-top: 80px; margin-left: 10px;">
            <div style="display: inline-block; text-align: center;">
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="display: block; float: left; margin: 0; padding: 0;"><br>
                <p style="font-weight: bold; margin: 0;"><u>{{ $kabagInfo->nama }}</u></p>
                <p style="margin: 0;">{{ $kabagInfo->pangkat }}</p>
                <p style="margin: 0;">NIP. {{ $kabagInfo->nip }}</p>
            </div>
        </div>
    </body>
</html>
