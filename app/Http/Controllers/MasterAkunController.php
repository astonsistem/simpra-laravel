<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterAkun;

class MasterAkunController extends Controller
{
    /**
     * Get list of master akun for klarifikasi langsung dropdown
     * Based on kredit/debit condition
     */
    public function listForKlarifikasi(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:kredit,debit',
                'search' => 'nullable|string',
            ]);

            $type = $request->input('type');
            $search = $request->input('search');

            $query = MasterAkun::query();

            // Filter based on type (kredit or debit)
            if ($type === 'kredit') {
                // Jika Kredit > 0: akun_kode LIKE '1%'
                $query->where('akun_kode', 'LIKE', '1%')->whereNotNull('rek_id');
            } else {
                // Jika Debit > 0: NOT akun_kode LIKE '1%' OR akun_kode LIKE '103%'
                $query->whereNotNull('rek_id')
                    ->where(function ($q) {
                    $q->where('akun_kode', 'NOT LIKE', '1%')
                      ->orWhere('akun_kode', 'LIKE', '103%');
                });
            }

            // Search functionality
            if (!empty($search)) {
                $query->whereNotNull('rek_id')
                    ->where(function ($q) use ($search) {
                    $q->where('akun_kode', 'ILIKE', "%$search%")
                      ->orWhere('akun_nama', 'ILIKE', "%$search%");
                });
            }

            $data = $query->select('akun_id', 'akun_kode', 'akun_nama')
                ->orderBy('akun_kode', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all master akun list
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search');

            $query = MasterAkun::query();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('akun_kode', 'ILIKE', "%$search%")
                      ->orWhere('akun_nama', 'ILIKE', "%$search%");
                });
            }

            $data = $query->select('akun_id', 'akun_kode', 'akun_nama')
                ->orderBy('akun_kode', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
