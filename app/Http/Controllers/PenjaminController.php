<?php

namespace App\Http\Controllers;

use App\Models\Penjamin;
use Illuminate\Http\Request;

class PenjaminController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        return Penjamin::orderBy('penjamin_nama')->paginate($perPage);
    }

    public function list(Request $request)
    {
        $limit = 1000;

        $data = Penjamin::limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function sync()
    {
    }
}
