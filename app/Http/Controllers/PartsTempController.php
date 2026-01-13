<?php

namespace App\Http\Controllers;

use App\Models\wh\PartsTemp;
use Illuminate\Http\Request;

class PartsTempController extends Controller
{
    public function index()
    {
        $parts = PartsTemp::all();
        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }
}
