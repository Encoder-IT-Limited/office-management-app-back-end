<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $queries = User::with('roles', 'skills', 'uploads')->where('status', 'active')->withTrashed();

        $queries->when($request->has('user_type'), function ($query) use ($request) {
            $request->validate([
                'user_type' => 'required|array',
                'user_type.*' => 'required|in:client,developer,manager,admin',
            ]);
            return $query->whereHas('roles', function ($role) use ($request) {
                return $role->whereIn('slug', $request->user_type);
            });
        });

        $users = $queries->latest()->paginate($request->per_page ?? 25);
        return response()->json([
            'users'   => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string',
            'email'             => 'required|email|unique:users',
            'phone'             => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
            'password'          => 'required|confirmed',
            'designation'       => 'sometimes|required|string',
            'role_id'           => 'required|exists:roles,id',
            'skills.*.skill_id' => 'sometimes|required|exists:skills,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $validator->validated();
        $data['password'] =  Hash::make($data['password']);
        $user = User::create($data);

        if ($user) {
            $user->roles()->attach($request->role_id);
            $user->skills()->attach($request->skills);

            if ($request->has('document')) {
                $validator = Validator::make($request->all(), [
                    'document' => 'required|mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg,webp,avif|max:20480',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }

                $file = $request->file('document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $stored_path = $request->file('document')->storeAs('user/file/' . $user->id, $fileName, 'public');
                $user->uploads()->create([
                    'path' => $stored_path
                ]);
            }
        }

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $request->user_id,
            'phone'       => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
            'user_id'     => 'required|exists:users,id',
            'designation' => 'sometimes|required',
            'password'    => 'sometimes|required|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::findOrFail($request->user_id);

        $user->update($validator->validated());

        if (isset($request->role_id))
            $user->roles()->sync($request->role_id);
        if (isset($request->skills))
            $user->skills()->sync($request->skills);

        if ($request->has('document')) {
            $validator = Validator::make($request->all(), [
                'document' => 'required|mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg,webp,avif|max:20480',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            if ($user->uploads && Storage::disk('public')->exists($user->uploads[0]->path)) {
                Storage::disk('public')->delete($user->uploads[0]->path);
            }

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $stored_path = $request->file('document')->storeAs('user/file/' . $user->id, $fileName, 'public');
            $user->uploads()->create([
                'path' => $stored_path
            ]);
        }

        return response()->json([
            'status' => 'Success',
            'user'   => $user
        ], 201);
    }

    public function destroy($id)
    {
        User::destroy($id);

        return response()->json([
            'status' => 'Deleted Successfully',
        ], 200);
    }

    public function forceDestroy($id)
    {
        User::withTrashed()->find($id)->forceDelete();

        return response()->json([
            'status' => 'Deleted Successfully',
        ], 200);
    }

    public function restore(Request $request)
    {
        User::find($request->id)->withTrashed()->restore();

        return response()->json([
            'status' => 'Restore Successfully',
        ], 200);
    }

    public function details()
    {
        $user = User::with(['roles' => function ($role) {
            $role->with('permissions');
        }])->find(Auth::id());

        return response()->json([
            'status' => 'Success',
            'user' => $user
        ], 200);
    }
}