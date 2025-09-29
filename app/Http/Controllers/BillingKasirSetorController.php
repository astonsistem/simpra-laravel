<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillingKasirFormResource;
use App\Http\Resources\PenerimaanLainResource;
use App\Http\Resources\PenerimaanSelisihSimpleResource;
use App\Http\Resources\RekeningKoranListResource;
use App\Http\Resources\Selisih\DataTransaksiResource;
use App\Models\DataPenerimaanLain;
use App\Models\DataPenerimaanLayanan;
use App\Models\DataPenerimaanSelisih;
use App\Models\DataRekeningKoran;
use Illuminate\Http\Request;

class BillingKasirSetorController extends Controller
{
    public function show(string $rcId)
    {
        try{
            $rc = DataRekeningKoran::where('rc_id', $rcId)->first();

            if (!$rc) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $billingKasirItems = DataPenerimaanLayanan::where('rc_id', $rcId)->get();
            $dataPenerimaanSelisih = DataPenerimaanSelisih::where('rc_id', $rcId)->get();
            $dataPenerimaanLain = DataPenerimaanLain::where('rc_id', $rcId)
                ->with('sumber')
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Success.',
                'data' => [
                    'rekening_koran' => new RekeningKoranListResource($rc),
                    'billing_kasir' => BillingKasirFormResource::collection($billingKasirItems),
                    'penerimaan_selisih' => DataTransaksiResource::collection($dataPenerimaanSelisih),
                    'penerimaan_lain' => PenerimaanLainResource::collection($dataPenerimaanLain),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show0(string $rcId)
    {
        try{
            $rc = DataRekeningKoran::where('rc_id', $rcId)->first();

            if (!$rc) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }

            $billingKasirItems = DataPenerimaanLayanan::where('rc_id', $rcId)->get();
            $dataPenerimaanSelisih = DataPenerimaanSelisih::where('rc_id', $rcId)->get();
            $dataPenerimaanLain = DataPenerimaanLain::where('rc_id', $rcId)
                ->with('sumber')
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Success.',
                'data' => [
                    'rekening_koran' => new RekeningKoranListResource($rc),
                    'billing_kasir' => BillingKasirFormResource::collection($billingKasirItems),
                    'penerimaan_selisih' => PenerimaanSelisihSimpleResource::collection($dataPenerimaanSelisih),
                    'penerimaan_lain' => PenerimaanLainResource::collection($dataPenerimaanLain),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}