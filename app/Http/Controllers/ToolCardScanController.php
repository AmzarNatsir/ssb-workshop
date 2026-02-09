<?php

namespace App\Http\Controllers;

use App\Models\ToolCard;
use Illuminate\Http\Request;

class ToolCardScanController extends Controller
{
    /**
     * Scan tool card by barcode/UID
     *
     * @param string $barcode
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanToolCard($barcode)
    {
        try {
            // Find tool card by UID (which is employee NIK)
            $toolCard = ToolCard::with('employee')
                               ->where('uid', $barcode)
                               ->orWhere(function($query) use ($barcode) {
                                   $query->whereHas('employee', function($q) use ($barcode) {
                                       $q->where('nik', $barcode);
                                   });
                               })
                               ->first();

            if (!$toolCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tool card not found'
                ], 404);
            }

            // Check if approved
            if ($toolCard->status !== 'APPROVED_FINAL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tool card is not approved. Current status: ' . $toolCard->status
                ], 403);
            }

            // Return employee and tool card data
            return response()->json([
                'success' => true,
                'data' => [
                    'tool_card_id' => $toolCard->id,
                    'employee' => [
                        'id' => $toolCard->employee->id,
                        'nik' => $toolCard->employee->nik,
                        'name' => $toolCard->employee->name,
                        'position' => $toolCard->employee->position,
                        'department' => $toolCard->employee->department,
                        'photo_url' => $toolCard->employee->photo_url,
                    ],
                    'access_level' => $toolCard->access_level,
                    'tool_categories' => $toolCard->tool_categories,
                    'status' => $toolCard->status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scanning tool card: ' . $e->getMessage()
            ], 500);
        }
    }
}
