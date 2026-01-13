<?php

namespace App\Http\Controllers;

use App\Models\MasterComponentTrouble;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterComponentTroubleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master_component_troubles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master_component_troubles.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'component_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MasterComponentTrouble::create([
            'uid' => Str::uuid(),
            'component_name' => $request->component_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Component created successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $component = MasterComponentTrouble::findOrFail($id);
        return view('master_component_troubles.edit', compact('component'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $component = MasterComponentTrouble::findOrFail($id);

        $request->validate([
            'component_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $component->update([
            'component_name' => $request->component_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Component updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $component = MasterComponentTrouble::findOrFail($id);
        $component->delete();
        return response()->json(['success' => true, 'message' => 'Component deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $components = MasterComponentTrouble::all();
        return response()->json([
            'data' => $components->map(function ($item) {
                return [
                    'id' => $item->id,
                    'component_name' => $item->component_name,
                    'description' => $item->description ?? '-',
                    'created_at' => $item->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
