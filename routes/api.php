<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/data', function (Request $request) {
    return response()->json([
        'app' => 'app1',
        'endpoint' => 'GET /api/data',
        'message' => 'Data retrieved successfully',
        'data' => [
            ['id' => 1, 'value' => 'Sample data 1'],
            ['id' => 2, 'value' => 'Sample data 2'],
            ['id' => 3, 'value' => 'Sample data 3'],
        ],
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::post('/data', function (Request $request) {
    return response()->json([
        'app' => 'app1',
        'endpoint' => 'POST /api/data',
        'message' => 'Data received successfully',
        'received_data' => $request->all(),
        'timestamp' => now()->toIso8601String()
    ], 201);
});
