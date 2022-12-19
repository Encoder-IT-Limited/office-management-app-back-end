<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status'   => 'Success',
            'roles' => $roles
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|unique:roles',
            'permission_id' => 'sometimes|required|exists:permissions,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        if ($role) {
            $role->permissions()->attach($request->permission_id);
        }

        return response()->json([
            'status' => 'Success',
            'role'   => $role
        ], 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'status' => 'Success',
            'role'   => $role
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $role = Role::findOrFail($request->role_id);
        $role->name = $request->name;
        $role->slug = Str::slug($request->name);
        $role->save();

        if (isset($request->permission_id)) {
            $role->permissions()->sync($request->permission_id);
        }

        return response()->json([
            'status' => 'Success',
            'role'   => $role
        ], 201);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->Permissions()->delete();
        $role->delete();

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }

    public function allPermissions(Request $request)
    {
        $permissions = Permission::latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status'   => 'Success',
            'permissions' => $permissions
        ], 200);
    }

    public function permissionStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'status' => 'Success',
            'permission'   => $permission
        ], 201);
    }
}
