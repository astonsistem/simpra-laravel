<?php

namespace App\Http\Controllers;

use App\Models\Kasir;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        return Kasir::orderBy('kasir_nama')->paginate($perPage);
    }

    public function list(Request $request)
    {
        $limit = 1000;

        $data = Kasir::limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function sync()
    {
    }
}
