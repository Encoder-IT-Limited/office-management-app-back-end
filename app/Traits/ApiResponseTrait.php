<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function success($message = '', $data = [], $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function failure($message = '', $status = 422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
