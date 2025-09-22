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
                <td rowspan="5" colspan="7" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 12px; font-family: 'Times New Roman', Times, serif;">{!! $title !!}<br>No. {{ $laporanData[0]->nomor }}</td>
            </tr>
            <tr></tr><tr></tr><tr></tr><tr></tr><tr></tr>
            <thead>
                <tr>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 30px; height: 40px;">No</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 260px; height: 40px;">Cara Bayar</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 150px; height: 40px;">UM - Pendapatan</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 150px; height: 40px;">Kas - Pendapatan</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 150px; height: 40px;">Piutang Perorangan</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 150px; height: 40px;">Piutang Jaminan</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; width: 150px; height: 40px;">Total Pendapatan</td>
                </tr>
            </thead>
            @php
                $collectData = collect($laporanData);
            @endphp
            @foreach ($collectData as $row)
                <tr>
                    <td style="text-align: center; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $loop->iteration }}</td>
                    <td style="text-align: left; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->penjamin_nama }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->pdd ? number_format($row->pdd, 2, ",", ".") : '-' }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->pendapatan ? number_format($row->pendapatan, 2, ",", ".") : '-' }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->piutang_perorangan ? number_format($row->piutang_perorangan, 2, ",", ".") : '-' }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->piutang_penjaminan ? number_format($row->piutang_penjaminan, 2, ",", ".") : '-' }}</td>
                    <td style="text-align: right; vertical-align: top; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $row->total ? number_format($row->total, 2, ",", ".") : '-' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: center; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">Total</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('pdd') ? number_format($collectData->sum('pdd'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('pendapatan') ? number_format($collectData->sum('pendapatan'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('piutang_perorangan') ? number_format($collectData->sum('piutang_perorangan'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('piutang_penjaminan') ? number_format($collectData->sum('piutang_penjaminan'), 2, ",", ".") : '-' }}</td>
                <td style="text-align: right; vertical-align: middle; font-weight: bold; border: 2px solid black; font-size: 9px; font-family: 'Times New Roman', Times, serif; word-wrap: break-word; page-break-inside: avoid !important;">{{ $collectData->sum('total') ? number_format($collectData->sum('total'), 2, ",", ".") : '-' }}</td>
            </tr>
        </table>
    </body>
</html>
