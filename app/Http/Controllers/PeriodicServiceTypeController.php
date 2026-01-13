<?php

namespace App\Http\Controllers;


use App\Models\ref\PeriodicServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodicServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = PeriodicServiceType::count();
        return view('periodic-service-type.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('periodic-service-type.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:periodic_service_type,name',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['uid'] = Str::uuid();
        $data['slug'] = Str::slug($data['name']);

        PeriodicServiceType::create($data);

        return response()->json(['success' => true, 'message' => 'Periodic Service Type created successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $type = PeriodicServiceType::findOrFail($id);
        return view('periodic-service-type.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $type = PeriodicServiceType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:periodic_service_type,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['name']);

        $type->update($data);

        return response()->json(['success' => true, 'message' => 'Periodic Service Type updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $type = PeriodicServiceType::findOrFail($id);
        $type->delete();
        return response()->json(['success' => true, 'message' => 'Periodic Service Type deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $types = PeriodicServiceType::select(['id', 'name', 'description'])->get();
        return response()->json([
            'data' => $types->values()->map(function ($row, $key) {
                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $key + 1,
                    'name' => $row->name,
                    'description' => $row->description,
                    'action' => '
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light edit-btn" data-id="' . $row->id . '">
                                <i class="ti ti-edit"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-danger delete-btn" data-id="' . $row->id . '">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                    '
                ];
            })
        ]);
    }
}
