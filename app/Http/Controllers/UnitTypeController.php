<?php

namespace App\Http\Controllers;

use App\Models\common\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = UnitType::count();
        return view('common.unit_type.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.unit_type.add');
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

        UnitType::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Unit Type created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('unit-type.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $unitType = UnitType::findOrFail($id);
        return view('common.unit_type.edit', compact('unitType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unitType = UnitType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $unitType->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Unit Type updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unitType = UnitType::findOrFail($id);
        $unitType->delete();
        return response()->json(['success' => true, 'message' => 'Unit Type deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $unitTypes = UnitType::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $unitTypes->map(function ($unitType) {
                return [
                    'id' => $unitType->id,
                    'name' => $unitType->name,
                    'description' => $unitType->description,
                    'created' => $unitType->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
