<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StaffSkill;
use App\Models\User;
use App\Http\Requests\StaffSkillRequest;
use App\Http\Resources\StaffSkillResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Staff Skills",
 *     description="API Endpoints for staff skills management"
 * )
 */
class StaffSkillController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/staff/skills",
     *     summary="List all staff skills",
     *     tags={"Staff Skills"},
     *     @OA\Response(
     *         response=200,
     *         description="List of staff skills",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/StaffSkill")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $skills = StaffSkill::query()
            ->when($request->input('user_id'), fn($query, $userId) => $query->where('user_id', $userId))
            ->when($request->input('skill_name'), fn($query, $skillName) => $query->bySkill($skillName))
            ->when($request->input('level'), fn($query, $level) => $query->byLevel($level))
            ->when($request->boolean('active_only'), fn($query) => $query->active())
            ->when($request->boolean('needs_verification'), fn($query) => $query->needsVerification())
            ->with(['user', 'verifications'])
            ->get();

        return response()->json(StaffSkillResource::collection($skills));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/staff/skills",
     *     summary="Add a new staff skill",
     *     tags={"Staff Skills"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Skill added successfully",
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     )
     * )
     */
    public function store(StaffSkillRequest $request): JsonResponse
    {
        $skill = StaffSkill::create($request->validated());
        return response()->json(new StaffSkillResource($skill), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/staff/skills/{skill}",
     *     summary="Get staff skill details",
     *     tags={"Staff Skills"},
     *     @OA\Parameter(
     *         name="skill",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Staff skill details",
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     )
     * )
     */
    public function show(StaffSkill $skill): JsonResponse
    {
        return response()->json(new StaffSkillResource($skill->load(['user', 'verifications'])));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/staff/skills/{skill}",
     *     summary="Update staff skill details",
     *     tags={"Staff Skills"},
     *     @OA\Parameter(
     *         name="skill",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     )
     * )
     */
    public function update(StaffSkillRequest $request, StaffSkill $skill): JsonResponse
    {
        $skill->update($request->validated());
        return response()->json(new StaffSkillResource($skill));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/staff/skills/{skill}/verify",
     *     summary="Verify a staff skill",
     *     tags={"Staff Skills"},
     *     @OA\Parameter(
     *         name="skill",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="next_verification_date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill verified successfully",
     *         @OA\JsonContent(ref="#/components/schemas/StaffSkill")
     *     )
     * )
     */
    public function verify(Request $request, StaffSkill $skill): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'next_verification_date' => 'nullable|date|after:today',
        ]);

        $skill->verifications()->create([
            'verified_by' => auth()->id(),
            'notes' => $validated['notes'],
            'verification_date' => now(),
            'next_verification_date' => $validated['next_verification_date'],
        ]);

        return response()->json(new StaffSkillResource($skill->load(['verifications'])));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/staff/skills/{skill}",
     *     summary="Delete a staff skill",
     *     tags={"Staff Skills"},
     *     @OA\Parameter(
     *         name="skill",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Skill deleted successfully"
     *     )
     * )
     */
    public function destroy(StaffSkill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(null, 204);
    }
} 