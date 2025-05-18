<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCategory;
use App\Http\Requests\MaintenanceCategoryRequest;
use App\Http\Resources\MaintenanceCategoryResource;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Maintenance Categories",
 *     description="API Endpoints for maintenance category management"
 * )
 */
class MaintenanceCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/categories",
     *     summary="List all maintenance categories",
     *     tags={"Maintenance Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MaintenanceCategory")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $categories = MaintenanceCategory::all();
        return response()->json(MaintenanceCategoryResource::collection($categories));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance/categories",
     *     summary="Create a new maintenance category",
     *     tags={"Maintenance Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceCategory")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceCategory")
     *     )
     * )
     */
    public function store(MaintenanceCategoryRequest $request): JsonResponse
    {
        $category = MaintenanceCategory::create($request->validated());
        return response()->json(new MaintenanceCategoryResource($category), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/categories/{category}",
     *     summary="Get category details",
     *     tags={"Maintenance Categories"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceCategory")
     *     )
     * )
     */
    public function show(MaintenanceCategory $category): JsonResponse
    {
        return response()->json(new MaintenanceCategoryResource($category));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/maintenance/categories/{category}",
     *     summary="Update category details",
     *     tags={"Maintenance Categories"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceCategory")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceCategory")
     *     )
     * )
     */
    public function update(MaintenanceCategoryRequest $request, MaintenanceCategory $category): JsonResponse
    {
        $category->update($request->validated());
        return response()->json(new MaintenanceCategoryResource($category));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/maintenance/categories/{category}",
     *     summary="Delete a category",
     *     tags={"Maintenance Categories"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully"
     *     )
     * )
     */
    public function destroy(MaintenanceCategory $category): JsonResponse
    {
        $category->delete();
        return response()->json(null, 204);
    }
} 