<?php

namespace App\Http\Controllers;

use App\Models\Loket;
use Illuminate\Http\Request;

class LoketController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 100);
        return Loket::orderBy('loket_id')->paginate($perPage);
    }

    public function list(Request $request)
    {
        $limit = 1000;

        $data = Loket::limit($limit)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    public function sync()
    {
    }
}
