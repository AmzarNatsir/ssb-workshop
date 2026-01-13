<?php

namespace App\Http\Controllers;

use App\Models\common\Racks;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Racks::count();
        return view('common.racks.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.racks.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rack_code' => 'required|string|max:50|unique:common_racks,rack_code',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
        ]);

        Racks::create([
            'uid' => Str::uuid(),
            'rack_code' => $request->rack_code,
            'name' => $request->name,
            'location' => $request->location,
            'responsible_person' => $request->responsible_person,
            'slug' => Str::slug($request->rack_code . ' ' . $request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Rack created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('racks.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rack = Racks::findOrFail($id);
        return view('common.racks.edit', compact('rack'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rack = Racks::findOrFail($id);

        $request->validate([
            'rack_code' => 'required|string|max:50|unique:common_racks,rack_code,' . $id,
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'responsible_person' => 'nullable|string|max:255',
        ]);

        $rack->update([
            'rack_code' => $request->rack_code,
            'name' => $request->name,
            'location' => $request->location,
            'responsible_person' => $request->responsible_person,
            'slug' => Str::slug($request->rack_code . ' ' . $request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Rack updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rack = Racks::findOrFail($id);
        $rack->delete();
        return response()->json(['success' => true, 'message' => 'Rack deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $racks = Racks::select(['id', 'rack_code', 'name', 'location', 'responsible_person', 'created_at'])->get();
        return response()->json([
            'data' => $racks->map(function ($rack) {
                return [
                    'id' => $rack->id,
                    'rack_code' => $rack->rack_code,
                    'name' => $rack->name,
                    'location' => $rack->location,
                    'responsible_person' => $rack->responsible_person,
                    'created' => $rack->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
