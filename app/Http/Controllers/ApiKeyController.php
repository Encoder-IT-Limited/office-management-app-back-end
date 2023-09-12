<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiKeyController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $api_keys = ApiKey
            ::where('user_id', $request->user_id ?? Auth::id())
            ->latest()
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'api_keys'   => $api_keys
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'value'       => 'required|string',
            'icon'        => 'required|string|nullable',
            'provider'    => 'required|string|nullable',
            'user_id'     => 'sometimes|required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $api_key = new ApiKey();

        $api_key->name =  $validated['name'];
        $api_key->value =  $validated['value'];
        $api_key->user_id =  isset($validated['user_id']) ? $validated['user_id'] : $user->id;

        if (isset($request->icon)) $api_key->icon = $request->icon;
        if (isset($request->provider)) $api_key->provider = $request->provider;

        $api_key->save();

        return response()->json([
            'api_key'   => $api_key
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function show(ApiKey $apiKey)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function edit(ApiKey $apiKey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  \App\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiKey $apiKey)
    {
        //
    }
}
