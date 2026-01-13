<?php

namespace App\Http\Controllers;


use App\Models\PartRequirement;
use App\Models\PartRequirementDetail;
use App\Models\Equipments;
use App\Models\ref\PeriodicServiceType;
use App\Models\wh\PartsTemp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PartRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = PartRequirement::count();
        return view('part-requirements.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipments = Equipments::with('periodicServiceType')->get();
        $parts = PartsTemp::all();
        return view('part-requirements.create', compact('equipments', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => [
                'required',
                'exists:equipments,id',
                'unique:part_requirements,equipment_id',
            ],
            'parts' => 'required|array|min:1',
            'parts.*.part_id' => 'required|exists:parts_temp,id',
        ]);

        DB::beginTransaction();
        try {
            $requirement = PartRequirement::create([
                'uid' => Str::uuid(),
                'equipment_id' => $request->equipment_id,
                'description' => $request->description,
                'status' => 'Active',
            ]);

            foreach ($request->parts as $partDatum) {
                PartRequirementDetail::create([
                    'uid' => Str::uuid(),
                    'part_requirement_id' => $requirement->id,
                    'part_id' => $partDatum['part_id'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Part Requirement created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $requirement = PartRequirement::with(['details.part', 'equipment.periodicServiceType'])->findOrFail($id);
        $equipments = Equipments::with('periodicServiceType')->get();
        $parts = PartsTemp::all();
        return view('part-requirements.edit', compact('requirement', 'equipments', 'parts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $request->validate([
            'equipment_id' => [
                'required',
                'exists:equipments,id',
                'unique:part_requirements,equipment_id,' . $id,
            ],
            'parts' => 'required|array|min:1',
            'parts.*.part_id' => 'required|exists:parts_temp,id',
        ]);

        DB::beginTransaction();
        try {
            $requirement = PartRequirement::findOrFail($id);
            $requirement->update([
                'equipment_id' => $request->equipment_id,
                'description' => $request->description,
                'status' => $request->status ?? 'Active',
            ]);

            // Sync details: Delete all and recreate (easiest for now) or smart sync.
            // Going with delete and recreate for simplicity unless ID preservation is needed.
            $requirement->details()->delete();

            foreach ($request->parts as $partDatum) {
                PartRequirementDetail::create([
                    'uid' => Str::uuid(),
                    'part_requirement_id' => $requirement->id,
                    'part_id' => $partDatum['part_id'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Part Requirement updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $requirement = PartRequirement::findOrFail($id);
        $requirement->delete();
        return response()->json(['success' => true, 'message' => 'Part Requirement deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $requirements = PartRequirement::with(['equipment.periodicServiceType', 'details.part'])->get();
        return response()->json([
            'data' => $requirements->values()->map(function ($row, $key) {
                // Format parts list
                $parts = $row->details->map(function($detail) {
                    $partName = $detail->part ? $detail->part->name : '-';
                    return '<li class="d-flex align-items-center"><i class="ti ti-point-filled fs-10 me-1"></i>' . $partName . '</li>';
                })->implode('');

                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $key + 1,
                    // Equipment: Code - Name
                    'equipment_name' => $row->equipment ? $row->equipment->code . ' - ' . $row->equipment->name : '-',
                    'service_type' => ($row->equipment && $row->equipment->periodicServiceType) ? $row->equipment->periodicServiceType->name : '-',
                    'parts' => '<ul class="list-unstyled mb-0">' . $parts . '</ul>',
                    'description' => $row->description,
                    'status' => $row->status,
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
