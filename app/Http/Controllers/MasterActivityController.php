<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterActivity;

class MasterActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master_activities.index');
    }

    /**
     * DataTables Source
     */
    public function datatables(Request $request)
    {
        // Simple manual implementation consistent with RoleController
        $activities = MasterActivity::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $activities->map(function ($row, $index) {
                $btn = '<div class="d-flex gap-2">';
                $btn .= '<a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-sm btn-light-primary edit-btn" title="Edit"><i class="ti ti-pencil"></i></a>';
                $btn .= '<a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-sm btn-light-danger delete-btn" title="Delete"><i class="ti ti-trash"></i></a>';
                $btn .= '</div>';

                return [
                    'DT_RowIndex' => $index + 1,
                    'code' => $row->code,
                    'description' => $row->description,
                    'category' => $row->category,
                    'action' => $btn
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:master_activities,code|max:50',
            'description' => 'required|string',
            'category' => 'nullable|string',
        ]);

        MasterActivity::create($request->all());

        return response()->json(['success' => true, 'message' => 'Activity created successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function show($id)
    {
        $activity = MasterActivity::findOrFail($id);
        return response()->json($activity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|max:50|unique:master_activities,code,' . $id,
            'description' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $activity = MasterActivity::findOrFail($id);
        $activity->update($request->all());

        return response()->json(['success' => true, 'message' => 'Activity updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $activity = MasterActivity::findOrFail($id);
        $activity->delete();

        return response()->json(['success' => true, 'message' => 'Activity deleted successfully.']);
    }
}
