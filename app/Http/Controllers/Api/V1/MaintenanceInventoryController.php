<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceInventory;
use App\Http\Requests\MaintenanceInventoryRequest;
use App\Http\Resources\MaintenanceInventoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Maintenance Inventory",
 *     description="API Endpoints for maintenance inventory management"
 * )
 */
class MaintenanceInventoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/inventory",
     *     summary="List all inventory items",
     *     tags={"Maintenance Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="List of inventory items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MaintenanceInventory")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $inventory = MaintenanceInventory::query()
            ->when($request->input('low_stock'), fn($query) => $query->lowStock())
            ->when($request->input('needs_reorder'), fn($query) => $query->needsReorder())
            ->when($request->input('category'), fn($query, $category) => $query->byCategory($category))
            ->when($request->input('location'), fn($query, $location) => $query->byLocation($location))
            ->with('inventoryLogs')
            ->get();

        return response()->json(MaintenanceInventoryResource::collection($inventory));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance/inventory",
     *     summary="Create a new inventory item",
     *     tags={"Maintenance Inventory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     )
     * )
     */
    public function store(MaintenanceInventoryRequest $request): JsonResponse
    {
        $item = MaintenanceInventory::create($request->validated());
        return response()->json(new MaintenanceInventoryResource($item), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/inventory/{item}",
     *     summary="Get inventory item details",
     *     tags={"Maintenance Inventory"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item details",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     )
     * )
     */
    public function show(MaintenanceInventory $item): JsonResponse
    {
        return response()->json(new MaintenanceInventoryResource($item->load('inventoryLogs')));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/maintenance/inventory/{item}",
     *     summary="Update inventory item details",
     *     tags={"Maintenance Inventory"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     )
     * )
     */
    public function update(MaintenanceInventoryRequest $request, MaintenanceInventory $item): JsonResponse
    {
        $item->update($request->validated());
        return response()->json(new MaintenanceInventoryResource($item));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance/inventory/{item}/adjust",
     *     summary="Adjust inventory item quantity",
     *     tags={"Maintenance Inventory"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="number"),
     *             @OA\Property(property="type", type="string", enum={"in", "out"}),
     *             @OA\Property(property="reason", type="string"),
     *             @OA\Property(property="task_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quantity adjusted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceInventory")
     *     )
     * )
     */
    public function adjust(Request $request, MaintenanceInventory $item): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:in,out',
            'reason' => 'required|string',
            'task_id' => 'nullable|exists:maintenance_tasks,id'
        ]);

        $item->adjustStock(
            $validated['quantity'],
            $validated['type'],
            $validated['reason'],
            $validated['task_id'] ?? null
        );

        return response()->json(new MaintenanceInventoryResource($item->fresh()));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/maintenance/inventory/{item}",
     *     summary="Delete an inventory item",
     *     tags={"Maintenance Inventory"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Item deleted successfully"
     *     )
     * )
     */
    public function destroy(MaintenanceInventory $item): JsonResponse
    {
        $item->delete();
        return response()->json(null, 204);
    }
} 