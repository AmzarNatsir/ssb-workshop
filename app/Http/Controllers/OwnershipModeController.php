<?php

namespace App\Http\Controllers;

use App\Models\common\OwnershipMode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OwnershipModeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = OwnershipMode::count();
        return view('common.ownership_mode.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.ownership_mode.add');
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

        OwnershipMode::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Ownership Mode created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('ownership-mode.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ownershipMode = OwnershipMode::findOrFail($id);
        return view('common.ownership_mode.edit', compact('ownershipMode'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ownershipMode = OwnershipMode::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $ownershipMode->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Ownership Mode updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ownershipMode = OwnershipMode::findOrFail($id);
        $ownershipMode->delete();
        return response()->json(['success' => true, 'message' => 'Ownership Mode deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $ownershipModes = OwnershipMode::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $ownershipModes->map(function ($ownershipMode) {
                return [
                    'id' => $ownershipMode->id,
                    'name' => $ownershipMode->name,
                    'description' => $ownershipMode->description,
                    'created' => $ownershipMode->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
