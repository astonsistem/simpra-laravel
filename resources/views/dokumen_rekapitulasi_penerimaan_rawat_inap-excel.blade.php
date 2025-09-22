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
            @php
                $title = mb_convert_case($laporanTitle, MB_CASE_TITLE, "UTF-8");
                $title = str_replace('Rsud', 'RSUD', $title);
            @endphp
            <tr>
                <td rowspan="5" colspan="4" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 12px; font-family: 'Times New Roman', Times, serif;">{!! $title !!}<br>No. {{ $laporanData[0]->nomor }}</td>
            </tr>
            <tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>
            <thead>
                <tr>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 320px;">Tanggal Setor - Cara Bayar</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 160px;">Piutang</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 160px;">Uang Muka</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 160px;">Grand Total</td>
                </tr>
            </thead>
            @php
                $collectData = collect($laporanData);
                $grouped = $collectData->groupBy('tgl_setor');
            @endphp
            @foreach ($grouped as $tglSetor => $items)
                <tr>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ \Carbon\Carbon::parse($tglSetor)->format('d-m-Y') }}</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;"></td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;"></td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;"></td>
                </tr>
                @foreach ($items as $row)
                    <tr>
                        <td style="text-align: left; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->penjamin_nama }}</td>
                        <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->piutang ? number_format($row->piutang, 2, ",", ".") : '-' }}</td>
                        <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->pdd ? number_format($row->pdd, 2, ",", ".") : '-' }}</td>
                        <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->total ? number_format($row->total, 2, ",", ".") : '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr>
                <td style="text-align: left; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">Total</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('piutang') ? number_format($collectData->sum('piutang'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('pdd') ? number_format($collectData->sum('pdd'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('total') ? number_format($collectData->sum('total'), 2, ",", ".") : '-' }}</td>
            </tr>
        </table>
    </body>
</html>
