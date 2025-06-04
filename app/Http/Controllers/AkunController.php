<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use Illuminate\Http\Request;

class AkunController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 1000);
        return Akun::orderBy('akun_kode')->paginate($perPage);
    }

    public function show(string $id)
    {
        $akun = Akun::find($id);

        if (!$akun) {
            return response()->json([
                'message' => 'Akun not found'
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $akun
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'akun_id' => 'required|integer|unique:master_akun',
            'akun_kode' => 'required|string|unique:master_akun',
            'akun_nama' => 'required|string',
            'rek_id' => 'nullable|string',
            'rek_nama' => 'nullable|string',
            'akun_kelompok' => 'nullable|string',
        ]);

        $akun = Akun::create($validated);
        return response()->json($akun, 201);
    }

    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);

        $validated = $request->validate([
            'akun_id' => 'integer|unique:master_akun,akun_id,' . $id . ',id',
            'akun_kode' => 'string|unique:master_akun,akun_kode,' . $id . ',id',
            'akun_nama' => 'string',
            'rek_id' => 'nullable|string',
            'rek_nama' => 'nullable|string',
            'akun_kelompok' => 'nullable|string',
        ]);

        $akun->update($validated);
        return response()->json($akun);
    }

    public function destroy(string $id)
    {
        $akun = Akun::findOrFail($id);
        $akun->delete();

        return response()->json(['message' => 'Akun deleted successfully']);
    }

    public function list(Request $request)
    {
        $akunKode = $request->input('akun_kode');
        $akunKodePrefix = '4';
        $limit = 1000;

        $query = Akun::select('akun_id', 'akun_nama')
            ->where('rek_id', 'like', "$akunKodePrefix%")
            ->orderBy('akun_id');

        if ($akunKode) {
            $query->where('akun_kode', 'like', "%$akunKode%");
        }

        $data = $query->limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function listAkunPotensiLain(Request $request)
    {
        $akunKode = $request->input('akun_kode');
        $akunKodePrefix = '';
        $limit = 1000;

        $query = Akun::select('akun_id', 'akun_nama')
            ->where('rek_id', 'like', "$akunKodePrefix%")
            ->orderBy('id');

        if ($akunKode) {
            $query->where('akun_kode', 'like', "%$akunKode%");
        }

        $data = $query->limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function listPendapatan(Request $request)
    {
        $akunKode = $request->input('akun_kode');
        $akunKodePrefix = '102';
        $limit = 1000;

        $query = Akun::select('akun_id', 'akun_nama')
            ->where('rek_id', 'like', "$akunKodePrefix%")
            ->orderBy('akun_id');

        $data = $query->limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }
}
