<?php

namespace App\Http\Controllers;

use App\Models\InspectionForm;
use App\Models\InspectionSection;
use App\Models\InspectionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class InspectionFormController extends Controller
{
    /**
     * Display a listing of inspection forms
     */
    public function index()
    {
        return view('inspection-forms.index');
    }

    /**
     * DataTables AJAX endpoint
     */
    public function datatables()
    {
        $forms = InspectionForm::with('creator')->select('inspection_forms.*');

        return DataTables::of($forms)
            ->addColumn('action', function ($form) {
                $editBtn = '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $form->id . '"><i class="fa fa-edit"></i> Edit</button>';
                $previewBtn = '<button class="btn btn-sm btn-info preview-btn" data-id="' . $form->id . '"><i class="fa fa-eye"></i> Preview</button>';
                $duplicateBtn = '<button class="btn btn-sm btn-secondary duplicate-btn" data-id="' . $form->id . '"><i class="fa fa-copy"></i> Duplicate</button>';
                
                $publishBtn = '';
                if ($form->status === 'DRAFT') {
                    $publishBtn = '<button class="btn btn-sm btn-success publish-btn" data-id="' . $form->id . '"><i class="fa fa-check"></i> Publish</button>';
                }
                
                $archiveBtn = '';
                if ($form->status === 'PUBLISHED') {
                    $archiveBtn = '<button class="btn btn-sm btn-warning archive-btn" data-id="' . $form->id . '"><i class="fa fa-archive"></i> Archive</button>';
                }
                
                $deleteBtn = '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $form->id . '"><i class="fa fa-trash"></i> Delete</button>';
                
                return $editBtn . ' ' . $previewBtn . ' ' . $duplicateBtn . ' ' . $publishBtn . ' ' . $archiveBtn . ' ' . $deleteBtn;
            })
            ->addColumn('status_badge', function ($form) {
                $badges = [
                    'DRAFT' => '<span class="badge bg-secondary">Draft</span>',
                    'PUBLISHED' => '<span class="badge bg-success">Published</span>',
                    'ARCHIVED' => '<span class="badge bg-warning">Archived</span>',
                ];
                return $badges[$form->status] ?? '';
            })
            ->addColumn('created_by_name', function ($form) {
                return $form->creator ? $form->creator->name : '-';
            })->addColumn('created_at', function ($form) {
                return $form->created_at->format('Y-m-d');
            })
            ->rawColumns(['action', 'status_badge'])
            ->make(true);
    }

    /**
     * Show the form for creating a new inspection form
     */
    public function create()
    {
        return view('inspection-forms.builder');
    }

    /**
     * Store a newly created inspection form
     */
    public function store(Request $request)
    {
        $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'applicable_unit_category' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'sections.*.section_title' => 'required|string|max:255',
            'sections.*.items' => 'required|array|min:1',
            'sections.*.items.*.item_name' => 'required|string|max:255',
            'sections.*.items.*.input_type' => 'required|in:NUMBER,TEXT,GOOD_REPAIR_REPLACE_NA,YES_NO_NA,PASS_FAIL_NA,OK_FAULTY_NA,IMAGE,DATE,GOOD_OTHERS',
        ]);

        try {
            DB::beginTransaction();

            // Create form
            $form = InspectionForm::create([
                'form_title' => $request->form_title,
                'form_description' => $request->form_description,
                'applicable_unit_category' => $request->applicable_unit_category,
                'applicable_unit_ids' => $request->applicable_unit_ids,
                'created_by' => Auth::id(),
            ]);

            // Create sections and items
            foreach ($request->sections as $sectionIndex => $sectionData) {
                $section = InspectionSection::create([
                    'form_id' => $form->id,
                    'section_order' => $sectionIndex,
                    'section_title' => $sectionData['section_title'],
                    'section_description' => $sectionData['section_description'] ?? null,
                ]);

                foreach ($sectionData['items'] as $itemIndex => $itemData) {
                    InspectionItem::create([
                        'section_id' => $section->id,
                        'item_order' => $itemIndex,
                        'item_code' => $itemData['item_code'] ?? 'ITEM-' . ($itemIndex + 1),
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['item_description'] ?? null,
                        'input_type' => $itemData['input_type'],
                        'is_required' => $itemData['is_required'] ?? false,
                        'threshold_warning' => $itemData['threshold_warning'] ?? null,
                        'threshold_critical' => $itemData['threshold_critical'] ?? null,
                        'conditional_logic' => $itemData['conditional_logic'] ?? null,
                        'auto_action' => $itemData['auto_action'] ?? null,
                        'instruction' => $itemData['instruction'] ?? null,
                        'reference_image' => $itemData['reference_image'] ?? null,
                        'item_image' => $itemData['item_image'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form created successfully',
                'data' => $form
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified inspection form
     */
    public function show($id)
    {
        $form = InspectionForm::with(['sections.items'])->findOrFail($id);
        return response()->json($form);
    }

    /**
     * Show the form for editing the specified inspection form
     */
    public function edit($id)
    {
        $form = InspectionForm::with(['sections.items'])->findOrFail($id);
        return view('inspection-forms.builder', compact('form'));
    }

    /**
     * Update the specified inspection form
     */
    public function update(Request $request, $id)
    {
        $form = InspectionForm::findOrFail($id);

        // If form is published, create revision instead
        if ($form->status === 'PUBLISHED') {
            return $this->createRevision($form, $request);
        }

        $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'applicable_unit_category' => 'nullable|string',
            'sections' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Update form
            $form->update([
                'form_title' => $request->form_title,
                'form_description' => $request->form_description,
                'applicable_unit_category' => $request->applicable_unit_category,
                'applicable_unit_ids' => $request->applicable_unit_ids,
            ]);

            // Delete existing sections and items
            $form->sections()->delete();

            // Recreate sections and items
            foreach ($request->sections as $sectionIndex => $sectionData) {
                $section = InspectionSection::create([
                    'form_id' => $form->id,
                    'section_order' => $sectionIndex,
                    'section_title' => $sectionData['section_title'],
                    'section_description' => $sectionData['section_description'] ?? null,
                ]);

                foreach ($sectionData['items'] as $itemIndex => $itemData) {
                    InspectionItem::create([
                        'section_id' => $section->id,
                        'item_order' => $itemIndex,
                        'item_code' => $itemData['item_code'] ?? 'ITEM-' . ($itemIndex + 1),
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['item_description'] ?? null,
                        'input_type' => $itemData['input_type'],
                        'is_required' => $itemData['is_required'] ?? false,
                        'threshold_warning' => $itemData['threshold_warning'] ?? null,
                        'threshold_critical' => $itemData['threshold_critical'] ?? null,
                        'conditional_logic' => $itemData['conditional_logic'] ?? null,
                        'auto_action' => $itemData['auto_action'] ?? null,
                        'instruction' => $itemData['instruction'] ?? null,
                        'reference_image' => $itemData['reference_image'] ?? null,
                        'item_image' => $itemData['item_image'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form updated successfully',
                'data' => $form
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified inspection form
     */
    public function destroy($id)
    {
        try {
            $form = InspectionForm::findOrFail($id);
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish an inspection form
     */
    public function publish($id)
    {
        try {
            $form = InspectionForm::findOrFail($id);
            $form->publish();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form published successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive an inspection form
     */
    public function archive($id)
    {
        try {
            $form = InspectionForm::findOrFail($id);
            $form->archive();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form archived successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate an inspection form
     */
    public function duplicate($id)
    {
        try {
            $form = InspectionForm::findOrFail($id);
            $newForm = $form->createRevision();

            return response()->json([
                'success' => true,
                'message' => 'Inspection form duplicated successfully',
                'data' => $newForm
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate inspection form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview an inspection form
     */
    public function preview($id)
    {
        $form = InspectionForm::with(['sections.items'])->findOrFail($id);
        return view('inspection-forms.preview', compact('form'));
    }

    /**
     * Create revision of published form
     */
    private function createRevision($form, $request)
    {
        try {
            DB::beginTransaction();

            $newForm = $form->createRevision();

            // Update with new data
            $newForm->update([
                'form_title' => $request->form_title,
                'form_description' => $request->form_description,
                'applicable_unit_category' => $request->applicable_unit_category,
                'applicable_unit_ids' => $request->applicable_unit_ids,
            ]);

            // Delete and recreate sections
            $newForm->sections()->delete();

            foreach ($request->sections as $sectionIndex => $sectionData) {
                $section = InspectionSection::create([
                    'form_id' => $newForm->id,
                    'section_order' => $sectionIndex,
                    'section_title' => $sectionData['section_title'],
                    'section_description' => $sectionData['section_description'] ?? null,
                ]);

                foreach ($sectionData['items'] as $itemIndex => $itemData) {
                    InspectionItem::create([
                        'section_id' => $section->id,
                        'item_order' => $itemIndex,
                        'item_code' => $itemData['item_code'] ?? 'ITEM-' . ($itemIndex + 1),
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['item_description'] ?? null,
                        'input_type' => $itemData['input_type'],
                        'is_required' => $itemData['is_required'] ?? false,
                        'threshold_warning' => $itemData['threshold_warning'] ?? null,
                        'threshold_critical' => $itemData['threshold_critical'] ?? null,
                        'conditional_logic' => $itemData['conditional_logic'] ?? null,
                        'auto_action' => $itemData['auto_action'] ?? null,
                        'instruction' => $itemData['instruction'] ?? null,
                        'reference_image' => $itemData['reference_image'] ?? null,
                        'item_image' => $itemData['item_image'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'New revision created successfully (original form is published)',
                'data' => $newForm
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create revision: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload item image for builder
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('inspection_items', 'public');

                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'data' => [
                        'path' => $path,
                        'url' => asset('storage/' . $path)
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }
}
