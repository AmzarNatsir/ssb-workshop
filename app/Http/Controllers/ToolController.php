<?php

namespace App\Http\Controllers;

use App\Models\Tools;
use App\Models\common\Racks;
use App\Models\common\ToolType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Tools::count();
        $racks = Racks::all();
        $toolTypes = ToolType::all();
        $statuses = \App\Models\common\Status::all();
        return view('tools.index', compact('count', 'racks', 'toolTypes', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $racks = Racks::all();
        $toolTypes = ToolType::all();
        $statuses = \App\Models\common\Status::all();
        return view('tools.add', compact('racks', 'toolTypes', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:tools,code',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'acquisition_date' => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric',
            'quantity' => 'nullable|integer',
            'min_quantity' => 'nullable|integer',
            'racks_id' => 'required|exists:common_racks,id',
            'tool_type_id' => 'required|exists:common_tool_type,id',
            'status_id' => 'nullable|exists:common_status,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $data['uid'] = Str::uuid();
        $data['print_barcode'] = $request->has('print_barcode');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('tools', 'public');
            $data['image'] = $path;
        }

        Tools::create($data);

        return response()->json(['success' => true, 'message' => 'Tool created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('tools.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tool = Tools::findOrFail($id);
        $racks = Racks::all();
        $toolTypes = ToolType::all();
        $statuses = \App\Models\common\Status::all();
        return view('tools.edit', compact('tool', 'racks', 'toolTypes', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tool = Tools::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:tools,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'acquisition_date' => 'nullable|date',
            'acquisition_cost' => 'nullable|numeric',
            'quantity' => 'nullable|integer',
            'min_quantity' => 'nullable|integer',
            'racks_id' => 'required|exists:common_racks,id',
            'tool_type_id' => 'required|exists:common_tool_type,id',
            'status_id' => 'nullable|exists:common_status,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $data['print_barcode'] = $request->has('print_barcode');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($tool->image) {
                Storage::disk('public')->delete($tool->image);
            }
            $image = $request->file('image');
            $path = $image->store('tools', 'public');
            $data['image'] = $path;
        }

        $tool->update($data);

        return response()->json(['success' => true, 'message' => 'Tool updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tool = Tools::findOrFail($id);
        
        // Delete image
        if ($tool->image) {
            Storage::disk('public')->delete($tool->image);
        }

        $tool->delete();
        return response()->json(['success' => true, 'message' => 'Tool deleted successfully.']);
    }

    /**
     * Print Labels
     */
    public function printLabels(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $request->ids);
        $tools = Tools::with(['racks', 'tool_type'])->whereIn('id', $ids)->get();

        return view('tools.print-labels', compact('tools'));
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $tools = Tools::with(['racks', 'tool_type', 'status'])->select(['id', 'image', 'code', 'name', 'tool_type_id', 'racks_id', 'quantity', 'min_quantity', 'status_id'])->get();
        return response()->json([
            'data' => $tools->map(function ($tool) {
                return [
                    'id' => $tool->id,
                    'image_url' => $tool->image ? Storage::url($tool->image) : null,
                    'code' => $tool->code,
                    'name' => $tool->name,
                    'tool_type' => $tool->tool_type ? $tool->tool_type->name : '-',
                    'rack' => $tool->racks ? $tool->racks->name : '-',
                    'quantity' => $tool->quantity,
                    'min_quantity' => $tool->min_quantity,
                    'status' => $tool->status ? $tool->status->name : '-',
                    'status_color' => $tool->status && str_contains(strtolower($tool->status->name), 'available') ? 'success' : 'warning',
                    'is_low_stock' => $tool->quantity <= $tool->min_quantity,
                ];
            })
        ]);
    }

    public function monitoring()
    {
        $racks = Racks::with(['tools' => function($query) {
            $query->with('status');
        }])->get();
        
        return view('tools.monitoring', compact('racks'));
    }
}
