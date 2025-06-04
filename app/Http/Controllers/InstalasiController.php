<?php

namespace App\Http\Controllers;

use App\Models\Instalasi;
use Illuminate\Http\Request;

class InstalasiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        return Instalasi::orderBy('instalasi_id')->paginate($perPage);
    }

    public function list(Request $request)
    {
        $limit = 1000;

        $data = Instalasi::limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function sync()
    {
    }
}
