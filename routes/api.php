<?php

// Import necessary classes from Laravel's support facade
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TravelInfoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here we define all the routes related to travel information services.
| These routes are prefixed with 'api' and are stateless by default.
|
*/

// Route to fetch weather forecast for a specific city
// Example: GET /api/forecast/Tokyo
Route::get('/forecast/{city}', [TravelInfoController::class, 'getWeather'])
    ->name('forecast.show'); // Naming the route for easier reference

// Route to fetch places of interest in a specific city
// Example: GET /api/places/Tokyo
Route::get('/places/{city}', [TravelInfoController::class, 'getPlaces'])
    ->name('places.index'); // Naming the route for easier reference

// Optional test route to verify if the API is working
// Example: GET /api/test
Route::get('/test', function () {
    return response()->json(['message' => 'API routes are working!']);
})
    ->name('api.test'); // Naming the route for easier reference

