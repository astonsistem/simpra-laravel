<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use Illuminate\Http\Request;

class CaraBayarController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        return CaraBayar::orderBy('carabayar_nama')->paginate($perPage);
    }

    public function list(Request $request)
    {
        $limit = 1000;

        $data = CaraBayar::limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }
}
