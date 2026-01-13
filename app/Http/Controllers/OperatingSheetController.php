<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OperatingSheetController extends Controller
{
    public function index()
    {
        $count = 10;
        return view('operating_sheet.index', compact('count'));
    }
}
