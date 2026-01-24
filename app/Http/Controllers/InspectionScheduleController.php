<?php

namespace App\Http\Controllers;

use App\Models\InspectionSchedule;
use App\Models\InspectionForm;
use App\Models\Equipments;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class InspectionScheduleController extends Controller
{
    /**
     * Display a listing of inspection schedules
     */
    public function index()
    {
        $forms = InspectionForm::published()->get();
        $units = Equipments::all();
        return view('inspection-schedules.index', compact('forms', 'units'));
    }

    /**
     * DataTables AJAX endpoint
     */
    public function datatables()
    {
        $schedules = InspectionSchedule::with(['form', 'unit'])->select('inspection_schedules.*');

        return DataTables::of($schedules)
            ->addColumn('form_title', function ($schedule) {
                return $schedule->form->form_title ?? '-';
            })
            ->addColumn('unit_name', function ($schedule) {
                return $schedule->unit->name ?? '-';
            })
            ->addColumn('status_badge', function ($schedule) {
                return $schedule->is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('start_date', function ($schedule) {
                return $schedule->start_date ? $schedule->start_date->format('Y-m-d') : '-';
            })
            ->addColumn('end_date', function ($schedule) {
                return $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '-';
            })
            ->addColumn('schedule_time', function ($schedule) {
                return $schedule->schedule_time ?? '-';
            })
            ->addColumn('next_generation', function ($schedule) {
                return $schedule->next_generation_at ? $schedule->next_generation_at->format('Y-m-d') : '-';
            })
            ->addColumn('action', function ($schedule) {
                $toggleBtn = $schedule->is_active 
                    ? '<button class="btn btn-sm btn-warning deactivate-btn" data-id="' . $schedule->id . '">Deactivate</button>'
                    : '<button class="btn btn-sm btn-success activate-btn" data-id="' . $schedule->id . '">Activate</button>';
                
                $editBtn = '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $schedule->id . '">Edit</button>';
                $deleteBtn = '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $schedule->id . '">Delete</button>';
                
                return $editBtn . ' ' . $toggleBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'form_id' => 'required|exists:inspection_forms,id',
            'unit_id' => 'required|exists:equipments,id',
            'frequency' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'schedule_time' => 'nullable|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            // Check if form is published
            $form = InspectionForm::findOrFail($request->form_id);
            if ($form->status !== 'PUBLISHED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only published forms can be scheduled'
                ], 400);
            }

            $schedule = InspectionSchedule::create($request->all());

            // Check if generation is due immediately (e.g. start date is today or in the past)
            if ($schedule->next_generation_at <= now()) {
                $schedule->generateNextInspection();
            }

            return response()->json([
                'success' => true,
                'message' => 'Inspection schedule created successfully',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified schedule
     */
    public function show($id)
    {
        $schedule = InspectionSchedule::with(['form', 'unit'])->findOrFail($id);
        return response()->json($schedule);
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'frequency' => 'sometimes|in:DAILY,WEEKLY,MONTHLY',
            'schedule_time' => 'nullable|date_format:H:i:s',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            $schedule = InspectionSchedule::findOrFail($id);
            
            // Prepare data for update, converting empty strings to null
            $updateData = $request->only(['frequency', 'schedule_time', 'start_date', 'end_date']);
            if (isset($updateData['schedule_time']) && $updateData['schedule_time'] === '') {
                $updateData['schedule_time'] = null;
            }
            if (isset($updateData['end_date']) && $updateData['end_date'] === '') {
                $updateData['end_date'] = null;
            }
            
            $schedule->update($updateData);

            // Recalculate next_generation_at if frequency or start_date changed
            if ($request->has('frequency') || $request->has('start_date')) {
                $schedule->next_generation_at = $schedule->calculateNextGenerationDate();
                $schedule->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Inspection schedule updated successfully',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified schedule
     */
    public function destroy($id)
    {
        try {
            $schedule = InspectionSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inspection schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a schedule
     */
    public function activate($id)
    {
        try {
            $schedule = InspectionSchedule::findOrFail($id);
            $schedule->is_active = true;
            $schedule->save();

            return response()->json([
                'success' => true,
                'message' => 'Schedule activated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a schedule
     */
    public function deactivate($id)
    {
        try {
            $schedule = InspectionSchedule::findOrFail($id);
            $schedule->is_active = false;
            $schedule->save();

            return response()->json([
                'success' => true,
                'message' => 'Schedule deactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate schedule: ' . $e->getMessage()
            ], 500);
        }
    }
}
