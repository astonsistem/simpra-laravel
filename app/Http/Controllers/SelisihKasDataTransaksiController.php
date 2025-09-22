<?php

namespace App\Http\Controllers;

use App\Http\Resources\Selisih\DataTransaksiResource;
use App\Models\SelisihKas;
use Illuminate\Http\Request;

class SelisihKasDataTransaksiController extends Controller
{
    public function index (Request $request) {
        try {
            $params = $request->validate([]);

            $query = SelisihKas::query();

            return DataTransaksiResource::collection(SelisihKas::all());
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], $e->getCode() ?? 500);
        }
    }

    public function create ()
    {
        return new DataTransaksiResource(new SelisihKas());
    }
}
