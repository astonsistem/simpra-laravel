<?php

namespace App\Http\Controllers;

use App\Http\Resources\MasterPelaporanResource;
use App\Models\MasterPelaporan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PelaporanExport;
use PDF;
use File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PelaporanController extends Controller
{
    public function show(string $slug)
    {
        try {
            $namaLaporan = ucwords(str_replace('_', ' ', $slug));
            $laporan = MasterPelaporan::where('nama_laporan', 'ILIKE', "%$namaLaporan%")->firstOrFail();
            $laporan->params = $laporan->resolved_params;
            return new MasterPelaporanResource($laporan);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        try {
            $data = MasterPelaporan::orderBy('kode_laporan', 'asc')->get()->map(fn($item) => [
                'label' => $item->label,
                'to'    => $item->to,
            ]);

            return response()->json([
                'status' => "200",
                'message' => "success",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generate(Request $request, $id)
    {
        try {
            $laporan = MasterPelaporan::find($id);
            $params = collect($laporan->params)->map(fn($p) => (object) $p);

            // Create dynamic parameters for validator
            $rules = $params->pluck('key')->mapWithKeys(fn($key) => [$key => 'required'])->toArray();
            $messages = [
                'required' => ":Attribute Laporan ({$laporan->kode_laporan}) {$laporan->nama_laporan} tidak boleh kosong!",
            ];

            // Validate dynamic parameters
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()]);
            }

            // Prepare query bindings
            $bindings = collect($params)->mapWithKeys(function ($param) use ($request) {
                return [$param->key => $request->input($param->key)];
            })->toArray();

            // Query data for laporan
            $laporanData = DB::select($laporan->sql, $bindings);

            // If laporan data is empty
            if (empty($laporanData)) {
                return response()->json(['error' => ['<b>Laporan (' . $laporan->kode_laporan . ') ' . $laporan->nama_laporan . '</b> dengan filter yang sudah diisi tidak memiliki data!']]);
            }

            // Set laporan title with params
            $laporanTitle = $laporan->title;
            foreach ($params as $param) {
                $key = $param->key;
                $type = $param->type;
                $value = strtoupper($request[$key]);
                // If type param is month
                if ($type == 'month') {
                    $value = strtoupper(Carbon::parse("2024-{$value}-01")->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('F'));
                }
                // If type param is date
                if ($type == 'date') {
                    $value = strtoupper(Carbon::createFromFormat('Y-m-d', $value)->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('j F Y'));
                }
                // Set laporan title
                $laporanTitle = str_replace("$" . $key, $value, $laporanTitle);
            }

            // Get param for export
            $fileType = $request->tipe_file_pelaporan;
            $laporanSlug = $laporan->slug;
            $timestamp = time();
            $fileName = "laporan_{$laporanSlug}{$timestamp}." . $fileType;

            // If tipe file pelaporan is PDF
            if ($fileType == 'pdf') {
                // Page setting
                $pageSetting = json_decode($laporan->page_setting);
                // Get Kabag info
                $kabagInfo = json_decode(Setting::where('key', 'kabag_info')->first()->value);
                // Store exported pdf to spesific path
                PDF::loadView("{$laporanSlug}-pdf", [
                        'laporan' => $laporan, 'laporanData' => $laporanData, 'laporanTitle' => $laporanTitle, 'laporan_width' => 0, 'kabagInfo' => $kabagInfo
                    ])
                    ->setOption('dpi', $pageSetting->dpi)
                    ->setPaper($pageSetting->page, $pageSetting->orientation)
                    ->save(public_path('exports/' . $fileName));
            }else {
            // If tipe file pelaporan is excel
                // Store exported excel to spesific path
                Excel::store(new PelaporanExport("{$laporanSlug}-excel", $laporanTitle, $laporanData), $fileName, 'exports');
            }

            // Add count to download_count
            // PelaporanInfo::first()->increment('download_count');

            return response()->json([
                'success'  => true,
                'filetype' => $fileType,
                'filename' => $fileName,
                'url'      => url("/exports/{$fileName}"),
            ], 200);     
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }       
    }

    public function deleteTemp($filename)
    {
        try{
            File::delete(public_path('exports/' . $filename));

            return response()->json([
                'success' => true,
                'message' => 'success delete generated file',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
