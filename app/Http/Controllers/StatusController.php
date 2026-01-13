<?php

namespace App\Http\Controllers;

use App\Models\common\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Status::count();
        return view('common.status.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.status.add');
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

        Status::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Status created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('status.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $status = Status::findOrFail($id);
        return view('common.status.edit', compact('status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $status = Status::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $status->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $status = Status::findOrFail($id);
        $status->delete();
        return response()->json(['success' => true, 'message' => 'Status deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $statuses = Status::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $statuses->map(function ($status) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'description' => $status->description,
                    'created' => $status->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
