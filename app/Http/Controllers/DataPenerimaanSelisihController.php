<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataPenerimaanSelisih;

class DataPenerimaanSelisihController extends Controller
{
    public function index()
    {
        return response()->json(DataPenerimaanSelisih::all());
    }
    public function show($id)
    {
        return response()->json(DataPenerimaanSelisih::findOrFail($id));
    }
    public function store(Request $request)
    {
        // Validasi sesuai field yang ada di form
        $validated = $request->validate([
            'tgl_bukti'        => 'date',
            'tgl_setor'        => 'date',
            'no_setor'         => 'string',
            'nominal'          => 'numeric',
            'rek_dpa'          => 'string',
            'loket_kasir'      => 'string',
            'cara_pembayaran'  => 'string',
            'bank'             => 'string',
            'jenis'            => 'string',
        ]);

        // Simpan data ke tabel
        $data = DataPenerimaanSelisih::create($validated);

        return response()->json([
            'message' => 'Data Selisih Kurang Bayar/Setor berhasil disimpan',
            'data' => $data
        ], 201);
    }
}
