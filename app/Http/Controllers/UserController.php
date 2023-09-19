<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $year, $month, $date;

    public function __construct()
    {
        $this->year = Carbon::today()->format('Y');
        $this->month = Carbon::today()->format('m');
        $this->date = Carbon::today()->format('d');
    }

    public function index(Request $request)
    {
        $queries = User::filterdByPermissions()->withData()->where('status', 'active')->withTrashed();

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
            'user'   => Auth::user(),
            'projects'   => Auth::user()->projects,
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
            'skills.*.skill_id' => 'sometimes|required|exists:skills,id',
            'skills.*.experience' => 'sometimes|required|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
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
            'message'   => 'Successfully Added',
            'user'   => $user
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'message'   => 'Success',
            'user'   => $user
        ], 200);
    }

    public function update(Request $request)
    {
        $validated = $this->validateWith([
            'user_id'     => 'required|exists:users,id',
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $request->user_id,
            'phone'       => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
            'user_id'     => 'required|exists:users,id',
            'designation' => 'sometimes|required',
            'password'    => 'sometimes|required|confirmed'
        ]);

        $updatableData = $request->except('user_id');

        if ($request->has('password')) {
            $updatableData['password'] = Hash::make($request->get('password'));
        }

        $user = User::findOrFail($request->user_id);

        $user->update($updatableData);

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
            'user'   => $user
        ], 200);
    }

    public function updateOwnProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string',
            'email'       => 'sometimes|required|email|unique:users,email,' . $user->id,
            'username'       => 'sometimes|required|unique:users,username,' . $user->id,
            'phone'       => 'sometimes|required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
            'password' => [
                'sometimes',
                'required_with:current_password',
                'different:current_password',
                'confirmed',
            ],
            'current_password' => [
                'sometimes',
                'required',
                function ($_, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        return $fail(__('The current password is incorrect.'));
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $basic_fields = ['name', 'email', 'phone', 'username'];
        foreach ($basic_fields as $field) {
            $value = $request->get($field);
            if (isset($value)) {
                $user->{$field} = $value;
            }
        }

        if (isset($request->password)) {
            $user->password =  Hash::make(
                $request->password
            );
        }

        $user->save();

        return response()->json([
            'user' => $user
        ], 200);
    }

    public function destroy($id)
    {
        User::destroy($id);

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }

    public function updateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status'      => 'required|in:active,inactive'
        ]);

        $user = User::whereId($request->user_id)->update(['status' => $request->status]);

        return response()->json([
            'user' => $user,
            'message' => 'Status updated successfully'
        ], 200);
    }

    public function restore(Request $request)
    {
        User::withTrashed()->find($request->id)->restore();

        return response()->json([
            'message' => 'Restore Successfully',
        ], 200);
    }

    public function details()
    {
        $user = User::with(['apiKeys', 'roles' => function ($role) {
            $role->with('permissions');
        }, 'todayAttendance', 'breakTimes' => function ($breakQ) {
            $breakQ->whereDate('start_time', Carbon::today());
        }])->withCount(['attendances as delays_count' => function ($delayQ) {
            return $delayQ->whereYear('check_in', '=', $this->year)
                ->whereMonth('check_in', '=', $this->month)
                ->delay();
        }])->find(Auth::id());

        return response()->json([
            'user' => $user
        ], 200);
    }
}
