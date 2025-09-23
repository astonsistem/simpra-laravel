<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillingKasirFormResource;
use App\Http\Resources\RekeningKoranListResource;
use App\Models\DataPenerimaanLayanan;
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

            return response()->json([
                'status' => 200,
                'rekening_koran' => new RekeningKoranListResource($rc),
                'billing_kasir' => BillingKasirFormResource::collection($billingKasirItems),
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
