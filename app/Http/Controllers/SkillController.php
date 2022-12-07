<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::latest()->paginate(25);

        return response()->json([
            'status' => 'Success',
            'skills'   => $skills
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $skill = Skill::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'status' => 'Success',
            'skill'   => $skill
        ], 201);
    }

    public function show($id)
    {
        $skill = Skill::find($id);

        if (!$skill)
            return response()->json(['status' => 'skill Not Found'], 404);

        return response()->json([
            'status' => 'Success',
            'skill'   => $skill
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'skill_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $skill = Skill::find($request->skill_id);
        if (!$skill)
            return response()->json(['status' => 'skill Not Found'], 404);

        $skill->name  = $request->name;
        $skill->slug = Str::slug($request->name);
        $skill->save();

        return response()->json([
            'status' => 'Success',
            'skill'   => $skill
        ], 201);
    }

    public function destroy($id)
    {
        $skill = Skill::find($id);

        if (!$skill)
            return response()->json(['status' => 'skill Not Found'], 404);

        $skill->delete();

        return response()->json([
            'status' => 'Deleted Success',
        ], 200);
    }
}
