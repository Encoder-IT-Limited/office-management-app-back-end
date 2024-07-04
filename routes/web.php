<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//function carAuth(): string
//{
//    $token = env('CARAPI_TOKEN');
//    $secret = env('CARAPI_SECRET');
//
//    $response = Http::withHeaders([
//        'X-Original' => 'foo',
//        'Accept' => 'text/plain',
//        'Content-type' => 'application/json',
//    ])->post('https://carapi.app/api/auth/login', [
//        'api_token' => $token,
//        'api_secret' => $secret,
//    ]);
//
//    $token = $response->body();
//
//    $filePath = storage_path('app/carapi_token.txt');
//    file_put_contents($filePath, $token);
//
//    return $token;
//}
//
//function getToken()
//{
//    $filePath = storage_path('app/carapi_token.txt');
//
//    if (!file_exists($filePath)) {
//        $file = fopen($filePath, 'w');
//        fclose($file);
//    }
//    $jwt = file_get_contents($filePath);
//    if (empty($jwt)) $token = carAuth();
//    else $token = $jwt;
//
//    if (!$token) {
//        return response()->json(['error' => 'Unauthorized'], 401);
//    }
//
//    $pieces = explode('.', $token);
//    if (count($pieces) !== 3) {
//        return response()->json(['error' => 'Unauthorized'], 401);
//    }
//
//    $payload = base64_decode($pieces[1]);
//    $data = json_decode($payload); // handle json decode exceptions
//
//    if ((new DateTime('now', new DateTimeZone('America/New_York')))->getTimestamp() > $data->exp) {
//        $token = carAuth();
//    }
//
//    return $token;
//}
//
//Route::get('/cars-api/{endpoint}', function ($endpoint) {
//    $token = getToken();
//    if ($token) {
//        if (!in_array($endpoint, ['years', 'makes', 'models', 'trims', 'options'])) {
//            return response()->json(['error' => 'Invalid endpoint'], 400);
//        }
//        $query['year'] = request('year') ?? '';
//        $query['make'] = request('make') ?? '';
//        $query['model'] = request('model') ?? '';
//
//        $response = Http::withHeaders([
//            'X-Original' => 'foo',
//            'Accept' => 'text/plain',
//            'Content-type' => 'application/json',
//            'Authorization' => 'Bearer ' . $token,
//        ])->get('https://carapi.app/api/' . $endpoint . '?' . http_build_query($query));
//        return $response->json();
//    }
//    return response()->json(['error' => 'Unauthorized'], 401);
//})->name('cars-api');

