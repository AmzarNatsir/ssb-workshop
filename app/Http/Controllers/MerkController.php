<?php

namespace App\Http\Controllers;

use App\Models\common\Merk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MerkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Merk::count();
        return view('common.merk.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.merk.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Merk::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Merk created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('merk.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $merk = Merk::findOrFail($id);
        return view('common.merk.edit', compact('merk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $merk = Merk::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $merk->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Merk updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $merk = Merk::findOrFail($id);
        $merk->delete();
        return response()->json(['success' => true, 'message' => 'Merk deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $merks = Merk::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $merks->map(function ($merk) {
                return [
                    'id' => $merk->id,
                    'name' => $merk->name,
                    'description' => $merk->description,
                    'created' => $merk->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
