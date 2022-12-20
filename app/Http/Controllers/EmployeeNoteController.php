<?php

namespace App\Http\Controllers;

use App\Models\EmployeeNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeNoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = EmployeeNote::with('users')->latest()->paginate($request->per_page ?? 25);

        return response()->json([
            'status'   => 'Success',
            'notes' => $notes
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'is_positive' => 'required|boolean',
            'note'        => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $data = $validator->validated();
        $note = EmployeeNote::create($data);

        return response()->json([
            'status'  => 'Success',
            'note' => $note
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
