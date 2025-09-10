<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillingSwaSimpleResource;
use App\Http\Resources\RekeningKoranListResource;
use App\Models\DataPenerimaanLain;
use Illuminate\Http\Request;
use App\Models\DataRekeningKoran;

class PenerimaanLainSetorController extends Controller
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

            $billingKasirItems = DataPenerimaanLain::where('rc_id', $rcId)->get();

            return response()->json([
                'status' => 200,
                'rekening_koran' => new RekeningKoranListResource($rc),
                'items' => BillingSwaSimpleResource::collection($billingKasirItems),
                'message' => 'Success.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
