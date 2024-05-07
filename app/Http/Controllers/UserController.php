<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserDetailsResource;
use App\Http\Resources\UserListResource;
use App\Models\Upload;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponseTrait;

    private $year, $month, $date;

    public function __construct()
    {
        $this->year = Carbon::today()->format('Y');
        $this->month = Carbon::today()->format('m');
        $this->date = Carbon::today()->format('d');
    }

    public function indexOld(Request $request)
    {
        $queries = User::filteredByPermissions()->withData()->where('status', 'active')->withTrashed();

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
            'user' => Auth::user(),
            'projects' => Auth::user()->projects,
            'users' => $users
        ], 200);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $users = User::with('children', 'roles', 'skills', 'uploads', 'notes');
            $users->when($request->has('user_type'), function ($query) use ($request) {
                $request->validate([
                    'user_type' => 'required|array',
                    'user_type.*' => 'required|in:client,developer,manager,admin',
                ]);

                return $query->whereHas('roles', function ($role) use ($request) {
                    return $role->whereIn('slug', $request->user_type);
                });
            });
        } else {
            $queries = User::filteredByPermissions()
                ->withData()
                ->where('status', 'active')
                ->withTrashed();
            $users = $queries;
        }

        $users = $users->withTrashed()->latest()->paginate($request->per_page ?? 25);
        return response()->json([
            'users' => $users,
            'user' => Auth::user(),
            'projects' => Auth::user()->projects,
        ], 200);
    }

    public function store(UserStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);

            if ($user) {
                $user->roles()->attach($request->role_id);
                $user->skills()->attach($request->skills);
                $user->children()->sync($request->users);

                if ($request->has('document')) {
                    $file = $request->file('document');
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $stored_path = $request->file('document')->storeAs('user/file/' . $user->id, $fileName, 'public');
                    $user->uploads()->create(['path' => $stored_path]);
                }

                $user->notes()->delete();
                if ($request->has('notes')) {
                    foreach ($request->notes as $note) {
                        $user->notes()->create(['note' => $note]);
                    }
                }
            }

            DB::commit();
            $user->load('parents', 'children', 'notes');
            return $this->success('User created successfully', new UserDetailsResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->failure('Something went wrong! ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $user->load('parents', 'children');
        return response()->json([
            'message' => 'Success',
            'user' => new UserDetailsResource($user)
        ], 200);
    }

    public function update(UserUpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $updatableData = $request->except('user_id');

            if ($request->has('password')) {
                $updatableData['password'] = Hash::make($request->get('password'));
            }

            $user = User::findOrFail($request->user_id);
            $user->update($updatableData);

            if (isset($request->role_id)) $user->roles()->sync($request->role_id);
            if (isset($request->skills)) $user->skills()->sync($request->skills);
            $user->children()->sync($request->users);

            if ($request->has('document')) {
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

            $user->notes()->delete();
            if ($request->has('notes')) {
                foreach ($request->notes as $note) {
                    $user->notes()->create([
                        'note' => $note
                    ]);
                }
            }

            DB::commit();
            $user->load('parents', 'children');
            return $this->success('User created successfully', new UserDetailsResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->failure('Something went wrong! ' . $e->getMessage(), 500);
        }
    }

    public function updateOwnProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'username' => 'sometimes|required|unique:users,username,' . $user->id,
            'phone' => 'sometimes|required|regex:/^([0-9\s\-\+\(\)]*)$/|min:11',
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
            $user->password = Hash::make(
                $request->password
            );
        }

        $user->save();

        return response()->json([
            'user' => $user
        ], 200);
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        $user->forceDelete();

        return response()->json([
            'message' => 'Deleted Successfully',
        ], 200);
    }

    public function updateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:active,inactive'
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
