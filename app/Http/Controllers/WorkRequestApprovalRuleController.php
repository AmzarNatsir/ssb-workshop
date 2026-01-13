<?php

namespace App\Http\Controllers;

use App\Models\WorkRequestApprovalRule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class WorkRequestApprovalRuleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $categories = [
            'On-Project – Operation',
            'Non-Project – Operation',
            'Non-Project – Non-Operation',
            'Non-Asset',
            'Project',
            'Department'
        ];
        $types = ['Repair Request', 'Goods Request'];
        
        return view('work_requests.approval_rules.index', compact('roles', 'categories', 'types'));
    }

    public function datatables()
    {
        $rules = WorkRequestApprovalRule::with('role')->get();

        return response()->json([
            'data' => $rules->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'category' => $rule->category,
                    'wr_type' => $rule->wr_type,
                    'role_name' => $rule->role->name ?? '-',
                    'step_order' => $rule->step_order,
                    'action' => '
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-icon btn-light edit-rule-btn" data-id="' . $rule->id . '">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-light text-danger delete-rule-btn" data-id="' . $rule->id . '">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    '
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'wr_type' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'step_order' => 'required|integer|min:1',
        ]);

        WorkRequestApprovalRule::create($request->all());

        return response()->json(['success' => true, 'message' => 'Approval rule created successfully.']);
    }

    public function edit($id)
    {
        $rule = WorkRequestApprovalRule::findOrFail($id);
        return response()->json(['success' => true, 'data' => $rule]);
    }

    public function update(Request $request, $id)
    {
        $rule = WorkRequestApprovalRule::findOrFail($id);
        
        $request->validate([
            'category' => 'required|string',
            'wr_type' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'step_order' => 'required|integer|min:1',
        ]);

        $rule->update($request->all());

        return response()->json(['success' => true, 'message' => 'Approval rule updated successfully.']);
    }

    public function destroy($id)
    {
        $rule = WorkRequestApprovalRule::findOrFail($id);
        $rule->delete();

        return response()->json(['success' => true, 'message' => 'Approval rule deleted successfully.']);
    }
}
