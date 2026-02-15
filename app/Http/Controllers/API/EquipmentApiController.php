<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipments;
use OpenApi\Attributes as OA;

class EquipmentApiController extends Controller
{
    #[OA\Get(
        path: '/api/units',
        summary: 'Get all units',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'uid', type: 'string'),
                            new OA\Property(property: 'code', type: 'string'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'engine_no', type: 'string'),
                            new OA\Property(property: 'chassis_no', type: 'string'),
                            new OA\Property(property: 'plate_number', type: 'string'),
                            new OA\Property(property: 'pic_unit', type: 'integer')
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function allUnits()
    {
        $units = Equipments::select([
            'id',
            'uid',
            'code',
            'name',
            'engine_no',
            'chassis_no',
            'plate_number',
            'pic_unit'
        ])->get();

        return response()->json($units);
    }

    #[OA\Get(
        path: '/api/units/{id}',
        summary: 'Find unit by ID',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Unit ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'uid', type: 'string'),
                        new OA\Property(property: 'code', type: 'string'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'engine_no', type: 'string'),
                        new OA\Property(property: 'chassis_no', type: 'string'),
                        new OA\Property(property: 'plate_number', type: 'string'),
                        new OA\Property(property: 'pic_unit', type: 'integer')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Unit not found'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function show($id)
    {
        $unit = Equipments::select([
            'id',
            'uid',
            'code',
            'name',
            'engine_no',
            'chassis_no',
            'plate_number',
            'pic_unit'
        ])->findOrFail($id);

        return response()->json($unit);
    }

    #[OA\Get(
        path: '/api/units/uid/{uid}',
        summary: 'Find unit by UID',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'uid',
                in: 'path',
                required: true,
                description: 'Unit UID',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'uid', type: 'string'),
                        new OA\Property(property: 'code', type: 'string'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'engine_no', type: 'string'),
                        new OA\Property(property: 'chassis_no', type: 'string'),
                        new OA\Property(property: 'plate_number', type: 'string'),
                        new OA\Property(property: 'pic_unit', type: 'integer')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Unit not found'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function showByUid($uid)
    {
        $unit = Equipments::select([
            'id',
            'uid',
            'code',
            'name',
            'engine_no',
            'chassis_no',
            'plate_number',
            'pic_unit'
        ])->where('uid', $uid)->firstOrFail();

        return response()->json($unit);
    }
}
