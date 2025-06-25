<?php

namespace App\Http\Controllers;

use App\Http\Resources\BkuCollection;
use App\Http\Resources\BkuResource;
use App\Models\DataBku;
use App\Models\DataRincianBku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BkuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'page' => 'nullable|integer|min:1',
                'size' => 'nullable|integer|min:1',
                'tgl_awal' => 'nullable|string',
                'tgl_akhir' => 'nullable|string',
                'bulan_awal' => 'nullable|string',
                'bulan_akhir' => 'nullable|string',
                'year' => 'nullable|string',
                'periode' => 'nullable|string',
                'uraian' => 'nullable|string',
                'bank' => 'nullable|integer',
                'debit' => 'nullable|integer',
                'kredit' => 'nullable|integer',
                'kualifikasi' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1) ?? 1;
            $size = $request->input('size', 100) ?? 100;
            $tglAwal = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');
            $bulanAwal = $request->input('bulan_awal');
            $bulanAkhir = $request->input('bulan_akhir');
            $year = $request->input('year');
            $periode = $request->input('periode');
            $jenis = $request->input('jenis');
            $bkuId = $request->input('bku_id');

            $query = DataBku::query();

            if (!empty($tglAwal) && !empty($tglAkhir) && $periode == "tanggal") {
                $startDate = Carbon::parse($tglAwal)->startOfDay();
                $endDate = Carbon::parse($tglAkhir)->endOfDay();
                $query->whereBetween('tgl_bku', [$startDate, $endDate]);
            }
            if (!empty($bulanAwal) && !empty($bulanAkhir) && $periode === "bulan") {
                $query->whereBetween('tgl_bku', [(int)$bulanAwal, (int)$bulanAkhir]);
            }
            if (!empty($year)) {
                $query->whereYear('tgl_bku', (int)$year);
            }
            if (!empty($jenis)) {
                $query->where('jenis', $jenis);
            }
            if (!empty($bkuId)) {
                $query->where('bku_id', $bkuId);
            }

            $totalItems = $query->count();
            $items = $query->skip(($page - 1) * $size)->take($size)->orderBy('tgl_bku')->orderBy('no_bku')->get();

            $totalPages = ceil($totalItems / $size);

            return response()->json(
                new BkuCollection($items, $totalItems, $page, $size, $totalPages)
            );
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'loc' => ['query', $field],
                        'msg' => $message,
                        'type' => 'validation',
                    ];
                }
            }
            return response()->json([
                'detail' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bku(Request $request)
    {
        //
    }

    public function list(Request $request)
    {
        //
    }

    public function statistik(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $rekeningKoran = DataBku::where('bku_id', $id)->first();

            if (!$rekeningKoran) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json(
                new BkuResource($rekeningKoran)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $bku = DataBku::where('bku_id', $id)->firstOrFail();
            $bkuRincian = DataRincianBku::where('bku_id', $id)->firstOrFail();
            if (!$bku) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $bku->delete();
            $bkuRincian->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menghapus data'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyRincian(string $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'detail' => [
                        [
                            'loc' => ['path', 'id'],
                            'msg' => 'ID is required.',
                            'type' => 'validation'
                        ]
                    ]
                ], 422);
            }

            $bkuRincian = DataRincianBku::where('bku_id', $id)->firstOrFail();
            if (!$bkuRincian) {
                return response()->json([
                    'message' => 'Not found'
                ], 404);
            }

            $bkuRincian->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menghapus data'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
