<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelisihKasController extends Controller
{
    public function index()
    {
        return response()->json(DB::table('data_penerimaan_selisih')->get());
    }
    public function show($id)
    {
        $data = DB::table('data_penerimaan_selisih')->find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($data);
    }
    public function getById($id)
    {
        $data = DB::table('data_penerimaan_selisih')->where('id', $id)->first();
        if (!$data) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($data);
    }
    public function store(Request $request)
    {
        // Simpan data ke tabel manual (tanpa model/migration)
        DB::table('data_penerimaan_selisih')->insert([
            'no_bukti' => $request->no_bukti,
            'tanggal_bukti' => $request->tanggal_bukti,
            'tanggal_setor' => $request->tanggal_setor,
            'no_setor' => $request->no_setor,
            'nominal' => $request->nominal,
            'rekening_dpa' => $request->rekening_dpa,
            'loket_kasir' => $request->loket_kasir,
            'cara_pembayaran' => $request->cara_pembayaran,
            'bank' => $request->bank,
            'jenis' => $request->jenis
        ]);

        return response()->json(['message' => 'Data berhasil disimpan'], 201);
    }
}
