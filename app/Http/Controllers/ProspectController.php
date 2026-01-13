<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function list()
    {
        return view('prospects.index');
    }
}
