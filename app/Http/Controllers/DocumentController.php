<?php

namespace App\Http\Controllers;

use App\Models\common\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Documents::count();
        return view('common.documents.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.documents.add');
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

        Documents::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Document created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('documents.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $document = Documents::findOrFail($id);
        return view('common.documents.edit', compact('document'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $document = Documents::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $document->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Document updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document = Documents::findOrFail($id);
        $document->delete();
        return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $documents = Documents::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->name,
                    'description' => $document->description,
                    'created' => $document->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
