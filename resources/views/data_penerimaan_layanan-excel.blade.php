<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <table style="border-spacing: 0; margin: auto;">
            <tr>
                <td style="font-style: italic; font-size: 9px; font-family: 'Times New Roman', Times, serif;">Digenerate pada {{ \Carbon\Carbon::now()->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('H:i, l j F Y') }}</td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="10" colspan="6" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 12px; font-family: 'Times New Roman', Times, serif;">{!! $laporanTitle !!}</td>
            </tr>
            <tr></tr><tr></tr><tr></tr><tr></tr><tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>
            <thead>
                <tr>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">NOMOR PENDAFTARAN</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">NOMOR REKAM MEDIK</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">NAMA PASIEN</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">TANGGAL PELAYANAN</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">NAMA PENJAMIN</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 200px;">TOTAL</td>
                </tr>
                <tr></tr>
            </thead>
            @foreach($laporanData as $lpdata)
                <tr>
                    <td style="text-align: center; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $lpdata->no_pendaftaran }}</td>
                    <td style="text-align: center; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $lpdata->no_rekam_medik }}</td>
                    <td style="text-align: left; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $lpdata->pasien_nama }}</td>
                    <td style="text-align: center; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ \Carbon\Carbon::parse($lpdata->tgl_pelayanan)->format('d-m-Y') }}</td>
                    <td style="text-align: left; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $lpdata->penjamin_nama }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ number_format($lpdata->total, 0, ",", ".") }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
