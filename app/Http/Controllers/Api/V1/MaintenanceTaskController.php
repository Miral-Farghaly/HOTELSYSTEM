<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTask;
use App\Http\Requests\MaintenanceTaskRequest;
use App\Http\Resources\MaintenanceTaskResource;
use App\Services\MaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Maintenance Tasks",
 *     description="API Endpoints for maintenance task management"
 * )
 */
class MaintenanceTaskController extends Controller
{
    protected MaintenanceService $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/tasks",
     *     summary="List all maintenance tasks",
     *     tags={"Maintenance Tasks"},
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance tasks",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MaintenanceTask")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = MaintenanceTask::query()
            ->when($request->input('status'), fn($query, $status) => $query->where('status', $status))
            ->when($request->input('room_id'), fn($query, $roomId) => $query->where('room_id', $roomId))
            ->when($request->input('category_id'), fn($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->input('assigned_to'), fn($query, $assignedTo) => $query->where('assigned_to', $assignedTo))
            ->when($request->input('date_from'), fn($query, $dateFrom) => $query->where('scheduled_date', '>=', $dateFrom))
            ->when($request->input('date_to'), fn($query, $dateTo) => $query->where('scheduled_date', '<=', $dateTo))
            ->with(['room', 'category', 'assignedTo'])
            ->orderBy('scheduled_date')
            ->get();

        return response()->json(MaintenanceTaskResource::collection($tasks));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance/tasks",
     *     summary="Create a new maintenance task",
     *     tags={"Maintenance Tasks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     )
     * )
     */
    public function store(MaintenanceTaskRequest $request): JsonResponse
    {
        $task = $this->maintenanceService->createMaintenanceTask($request->validated());
        return response()->json(new MaintenanceTaskResource($task), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/maintenance/tasks/{task}",
     *     summary="Get task details",
     *     tags={"Maintenance Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task details",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     )
     * )
     */
    public function show(MaintenanceTask $task): JsonResponse
    {
        return response()->json(new MaintenanceTaskResource($task->load(['room', 'category', 'assignedTo'])));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/maintenance/tasks/{task}",
     *     summary="Update task details",
     *     tags={"Maintenance Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     )
     * )
     */
    public function update(MaintenanceTaskRequest $request, MaintenanceTask $task): JsonResponse
    {
        $task = $this->maintenanceService->updateMaintenanceTask($task, $request->validated());
        return response()->json(new MaintenanceTaskResource($task));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/maintenance/tasks/{task}/complete",
     *     summary="Complete a maintenance task",
     *     tags={"Maintenance Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task completed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceTask")
     *     )
     * )
     */
    public function complete(Request $request, MaintenanceTask $task): JsonResponse
    {
        $task = $this->maintenanceService->completeMaintenanceTask($task, $request->all());
        return response()->json(new MaintenanceTaskResource($task));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/maintenance/tasks/{task}",
     *     summary="Delete a task",
     *     tags={"Maintenance Tasks"},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully"
     *     )
     * )
     */
    public function destroy(MaintenanceTask $task): JsonResponse
    {
        $task->delete();
        return response()->json(null, 204);
    }
} 