<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Admin - User Management",
 *     description="API Endpoints for managing users (Admin only)"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     summary="Get all users",
     *     description="Returns a paginated list of all users",
     *     tags={"Admin - User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User is not an admin")
     * )
     */
    public function index()
    {
        $users = User::select(['id', 'name', 'email', 'created_at'])
            ->latest()
            ->paginate();

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/{user}/roles",
     *     summary="Assign role to user",
     *     description="Assigns a role to the specified user",
     *     tags={"Admin - User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="role", type="string", example="manager")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role assigned successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User is not an admin"),
     *     @OA\Response(response=404, description="User or role not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name'
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();
        $user->assignRole($role);

        return response()->json([
            'message' => 'Role assigned successfully'
        ]);
    }
} 