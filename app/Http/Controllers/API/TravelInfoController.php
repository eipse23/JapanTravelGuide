<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use App\Services\PlacesService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * TravelInfoController handles API requests related to travel information,
 * including weather and places data.
 */
class TravelInfoController extends Controller
{
    private WeatherService $weatherService;
    private PlacesService $placesService;

    /**
     * Create a new controller instance.
     *
     * @param WeatherService $weatherService
     * @param PlacesService $placesService
     */
    public function __construct(WeatherService $weatherService, PlacesService $placesService)
    {
        $this->weatherService = $weatherService;
        $this->placesService = $placesService;
    }

    /**
     * Get the weather information for a specified city.
     *
     * @param string $city
     * @return JsonResponse
     */
    public function getWeather(string $city): JsonResponse
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        // dd($apiKey);  // Uncomment to debug the API key value
        return response()->json($this->weatherService->getWeather($city));
    }

    /**
     * Get the places information for a specified city.
     *
     * @param string $city
     * @return JsonResponse
     */
    public function getPlaces(string $city): JsonResponse
    {
        try {
            $response = $this->placesService->getPlaces($city);
            Log::info('Places API Response:', ['data' => $response]);
            return response()->json($response);
        } catch (Exception $e) {
            Log::error('Error fetching places:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch places'], 500);
        }
    }
}
