<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function categoryIndex()
    {
        $count = 10; // Example count, replace with actual data retrieval logic
        return view('common.category.index', compact('count'));
    }

    public function merksIndex()
    {
        $count = 10; // Example count, replace with actual data retrieval logic
        return view('common.merk.index', compact('count'));
    }

    public function typeUnitIndex()
    {
        $count = 10; // Example count, replace with actual data retrieval logic
        return view('common.unit_type.index', compact('count'));
    }

    public function statusIndex()
    {
        $count = 10; // Example count, replace with actual data retrieval logic
        return view('common.status.index', compact('count'));
    }

    public function documentIndex()
    {
        $count = 10; // Example count, replace with actual data retrieval logic
        return view('common.documents.index', compact('count'));
    }
}
