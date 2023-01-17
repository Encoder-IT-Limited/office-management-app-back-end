<?php

namespace App\Http\Controllers;

use App\Models\EmployeeNote;
use App\Models\User;
use App\Traits\HasPermissionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeNoteController extends Controller
{
    use HasPermissionsTrait;
    public function index(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $employeeNotes = EmployeeNote::with('users');

        if ($user->hasRole(['manager', 'developer'])) {
            $employeeNotes->where('user_id', $user->id);
        }

        // $employeeNotes->latest()->paginate($request->per_page ?? 25);
        $query = $employeeNotes->latest()->paginate($request->per_page ?? 25);
        return response()->json([
            'status'   => 'Success',
            'notes' => $query
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'is_positive' => 'required|boolean',
            'note'        => 'required|string',
            'document'    => 'sometimes|required|mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg,webp,avif|max:20480',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $validator->validated();
        $employeeNote = EmployeeNote::create($data);

        if ($employeeNote) {
            if ($request->has('document')) {
                $validator = Validator::make($request->all(), [
                    'document' => 'required|mimes:doc,pdf,docx,zip,jpeg,png,jpg,gif,svg,webp,avif|max:20480',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
                if ($employeeNote->uploads && Storage::disk('public')->exists($employeeNote->uploads[0]->path)) {
                    Storage::disk('public')->delete($employeeNote->uploads[0]->path);
                }

                $file = $request->file('document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $stored_path = $request->file('document')->storeAs('employee_note/document/' . $employeeNote->id, $fileName, 'public');
                $employeeNote->uploads()->create([
                    'path' => $stored_path
                ]);
            }
        }

        return response()->json([
            'status'  => 'Success',
            'note' => $employeeNote
        ], 201);
    }

    public function show($id)
    {
        $note = EmployeeNote::findOrFail($id);

        return response()->json([
            'status'  => 'Success',
            'note' => $note
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'is_positive' => 'required|boolean',
            'note'        => 'required|string',
            'note_id'   => 'required|exists:employee_notes,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $note = EmployeeNote::findOrFail($request->note_id);
        $note->update($validator->validated());

        return response()->json([
            'status'  => 'Success',
            'note' => $note
        ], 201);
    }

    public function destroy($id)
    {
        EmployeeNote::destroy($id);

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
