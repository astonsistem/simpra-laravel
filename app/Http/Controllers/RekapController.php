<?php

namespace App\Http\Controllers;

use App\Models\RekapStatusrawatinapbpjsView;
use App\Models\RekapStatusrawatjalanbpjsView;
use Illuminate\Http\Request;

class RekapController extends Controller
{

    public function pasienRajalBpjs()
    {
        try {
            $rekap = RekapStatusrawatjalanbpjsView::all();

            if (!$rekap) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json($rekap, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pasienRanapBpjs()
    {
        try {
            $rekap = RekapStatusrawatinapbpjsView::all();

            if (!$rekap) {
                return response()->json([
                    'message' => 'Not found.'
                ], 404);
            }
            return response()->json($rekap, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
