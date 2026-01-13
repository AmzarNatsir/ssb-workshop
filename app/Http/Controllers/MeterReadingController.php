<?php

namespace App\Http\Controllers;

use App\Models\common\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = MeterReading::count();
        return view('common.meter_reading.index', compact('count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('common.meter_reading.add');
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

        MeterReading::create([
            'uid' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Meter Reading created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('meter-reading.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $meterReading = MeterReading::findOrFail($id);
        return view('common.meter_reading.edit', compact('meterReading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $meterReading = MeterReading::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $meterReading->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'message' => 'Meter Reading updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meterReading = MeterReading::findOrFail($id);
        $meterReading->delete();
        return response()->json(['success' => true, 'message' => 'Meter Reading deleted successfully.']);
    }

    /**
     * Get datatables data
     */
    public function datatables()
    {
        $meterReadings = MeterReading::select(['id', 'name', 'description', 'created_at'])->get();
        return response()->json([
            'data' => $meterReadings->map(function ($meterReading) {
                return [
                    'id' => $meterReading->id,
                    'name' => $meterReading->name,
                    'description' => $meterReading->description,
                    'created' => $meterReading->created_at->format('d M Y, h:i a')
                ];
            })
        ]);
    }
}
