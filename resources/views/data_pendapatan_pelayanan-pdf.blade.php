<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            hr {
                padding: 0px;
                margin: 0px;    
                opacity: 0.5;
            }
            .generate-info {
                font-style: italic; 
                font-size: 9px; 
                font-family: 'Times New Roman', Times, serif;
            }
            .table-header {
                text-align: center; 
                vertical-align: middle; 
                font-weight: bold; 
                border: 1px solid black; 
                font-size: 16px; 
                font-family: 'Times New Roman', Times, serif;
                word-wrap: break-word;
            }
            .table-data {
                text-align: center; 
                vertical-align: top; 
                border: 1px solid black; 
                font-size: 16px; 
                font-family: 'Times New Roman', Times, serif; 
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
        <div style="position: fixed; left: 0px; top: -70px; right: 0px;">
            <p class="generate-info">Digenerate pada {{ \Carbon\Carbon::now()->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('H:i, l j F Y') }}</p>
            <hr>
        </div>
        <h1 style="text-align: center; font-weight: bold; font-size: 18px; font-family: 'Times New Roman', Times, serif; margin-bottom: 30px; margin-top: 0;">{!! $laporanTitle !!}</h1>
        <table style="border-spacing: 0; margin: auto;">
            <thead>
                <tr>
                    <td class="table-header" style="width: 200px;">NOMOR PENDAFTARAN</td>
                    <td class="table-header" style="width: 200px;">NOMOR REKAM MEDIK</td>
                    <td class="table-header" style="width: 200px;">NAMA PASIEN</td>
                    <td class="table-header" style="width: 200px;">TANGGAL PELAYANAN</td>
                    <td class="table-header" style="width: 200px;">NAMA PENJAMIN</td>
                    <td class="table-header" style="width: 200px;">TOTAL</td>
                </tr>
                <tr></tr>
            </thead>
            @foreach($laporanData as $lpdata)
                <tr>
                    <td class="table-data">{{ $lpdata->no_pendaftaran }}</td>
                    <td class="table-data">{{ $lpdata->no_rekam_medik }}</td>
                    <td class="table-data text-left">{{ $lpdata->pasien_nama }}</td>
                    <td class="table-data">{{ \Carbon\Carbon::parse($lpdata->tgl_pelayanan)->format('d-m-Y') }}</td>
                    <td class="table-data text-left">{{ $lpdata->penjamin_nama }}</td>
                    <td class="table-data text-right">{{ number_format($lpdata->total, 0, ",", ".") }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
