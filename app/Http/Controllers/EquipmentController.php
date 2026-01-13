<?php

namespace App\Http\Controllers;

use App\Models\Equipments;
use App\Models\common\Category;
use App\Models\common\Merk;
use App\Models\common\UnitType;
use App\Models\common\Status;
use App\Models\common\MeterReading;
use App\Models\common\OwnershipMode;
use App\Models\User;
use App\Models\Supplier;
use App\Models\EquipmentDocument;
use App\Models\common\Documents;
use App\Models\ref\PeriodicServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = Equipments::count();
        $categories = Category::all();
        $merks = Merk::all();
        $unitTypes = UnitType::all();
        $periodicServiceTypes = PeriodicServiceType::all();
        return view('equipment.index', compact('count', 'categories', 'merks', 'unitTypes', 'periodicServiceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $merks = Merk::all();
        $unitTypes = UnitType::all();
        $statuses = Status::all();
        $meterReadings = MeterReading::all();
        $ownershipModes = OwnershipMode::all();
        $users = User::all();
        $suppliers = Supplier::all();
        $periodicServiceTypes = PeriodicServiceType::all();
        return view('equipment.add', compact('categories', 'merks', 'unitTypes', 'statuses', 'meterReadings', 'ownershipModes', 'users', 'suppliers', 'periodicServiceTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:equipments',
            'name' => 'required|string|max:200',
            'category_id' => 'nullable|exists:common_category,id',
            'merk_id' => 'nullable|exists:common_merk,id',
            'unit_type_id' => 'nullable|exists:common_unit_type,id',
            'equipment_status_id' => 'nullable|exists:common_status,id',
            'meter_reading_id' => 'nullable|exists:common_meter_reading,id',
            'ownership_mode_id' => 'nullable|exists:common_ownership_mode,id',
            'pic_unit' => 'nullable|exists:users,id',
            'periodic_service_type_id' => 'nullable|exists:periodic_service_type,id',
            'wh_per_project' => 'required|numeric|min:0.01',
            'warranty_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $data['uid'] = Str::uuid();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('equipments', 'public');
            $data['image'] = $path;
        }
        
        Equipments::create($data);

        return response()->json(['success' => true, 'message' => 'Equipment created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('equipment.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $equipment = Equipments::findOrFail($id);
        $categories = Category::all();
        $merks = Merk::all();
        $unitTypes = UnitType::all();
        $statuses = Status::all();
        $meterReadings = MeterReading::all();
        $ownershipModes = OwnershipMode::all();
        $users = User::all();
        $suppliers = Supplier::all();
        $periodicServiceTypes = PeriodicServiceType::all();
        return view('equipment.edit', compact('equipment', 'categories', 'merks', 'unitTypes', 'statuses', 'meterReadings', 'ownershipModes', 'users', 'suppliers', 'periodicServiceTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $equipment = Equipments::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:equipments,code,' . $id,
            'name' => 'required|string|max:200',
            'category_id' => 'nullable|exists:common_category,id',
            'merk_id' => 'nullable|exists:common_merk,id',
            'unit_type_id' => 'nullable|exists:common_unit_type,id',
            'equipment_status_id' => 'nullable|exists:common_status,id',
            'meter_reading_id' => 'nullable|exists:common_meter_reading,id',
            'ownership_mode_id' => 'nullable|exists:common_ownership_mode,id',
            'pic_unit' => 'nullable|exists:users,id',
            'periodic_service_type_id' => 'nullable|exists:periodic_service_type,id',
            'wh_per_project' => 'required|numeric|min:0.01',
            'warranty_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }

            $image = $request->file('image');
            $path = $image->store('equipments', 'public');
            $data['image'] = $path;
        }

        $equipment->update($data);

        return response()->json(['success' => true, 'message' => 'Equipment updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $equipment = Equipments::findOrFail($id);
        
        // Delete image if exists
        if ($equipment->image) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();
        return response()->json(['success' => true, 'message' => 'Equipment deleted successfully.']);
    }

    /**
     * Show documents management
     */
    public function documents($id)
    {
        $equipment = Equipments::with('documents.documentType')->findOrFail($id);
        $documentTypes = Documents::all();
        return view('equipment.documents', compact('equipment', 'documentTypes'));
    }

    /**
     * Upload document
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'document_type_id' => 'required|exists:common_documents,id',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $equipment = Equipments::findOrFail($request->equipment_id);
            
            // Store file
            $path = $file->store('equipment_documents/' . $equipment->code, 'public');

            // Create record
            EquipmentDocument::create([
                'equipment_id' => $request->equipment_id,
                'document_type_id' => $request->document_type_id,
                'document_path' => $path,
            ]);

            return response()->json(['success' => true, 'message' => 'Document uploaded successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

    /**
     * Delete document
     */
    public function deleteDocument($id)
    {
        $document = EquipmentDocument::findOrFail($id);
        
        // Delete file
        if ($document->document_path) {
            Storage::disk('public')->delete($document->document_path);
        }

        $document->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables(Request $request)
    {
        $query = Equipments::with(['category', 'merk', 'unitType', 'status', 'periodicServiceType']);

        // Filtering
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->merk_id) {
            $query->where('merk_id', $request->merk_id);
        }
        if ($request->unit_type_id) {
            $query->where('unit_type_id', $request->unit_type_id);
        }

        $equipments = $query->get();

        return response()->json([
            'data' => $equipments->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category' => $item->category ? $item->category->name : '-',
                    'merk' => $item->merk ? $item->merk->name : '-',
                    'unit_type' => $item->unitType ? $item->unitType->name : '-',
                    'status' => $item->status ? $item->status->name : '-',
                    'service_period' => $item->periodicServiceType ? $item->periodicServiceType->name : '-',
                    'plate_number' => $item->plate_number ?? '-',
                    'image' => $item->image,
                    'image_url' => $item->image ? Storage::url($item->image) : null,
                    'created' => $item->created_at ? $item->created_at->format('d M Y, h:i a') : '-'
                ];
            })
        ]);
    }
}

