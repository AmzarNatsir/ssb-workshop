<?php

namespace App\Http\Controllers;

use App\Models\common\ToolType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ToolTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = ToolType::count();
        return view('common.tool-types.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.tool-types.add');
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

        ToolType::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Tool Type created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('tool-type.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $toolType = ToolType::findOrFail($id);
        return view('common.tool-types.edit', compact('toolType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $toolType = ToolType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $toolType->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Tool Type updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $toolType = ToolType::findOrFail($id);
        $toolType->delete();
        return response()->json(['success' => true, 'message' => 'Tool Type deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $toolTypes = ToolType::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $toolTypes->map(function ($toolType) {
                return [
                    'id' => $toolType->id,
                    'name' => $toolType->name,
                    'description' => $toolType->description,
                    'created' => $toolType->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
