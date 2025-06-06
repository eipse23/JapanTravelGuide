<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Fetch the weather forecast for a given city.
     *
     * @param string $city The name of the city to retrieve weather data for.
     * @return array The weather data as an associative array.
     * @throws \Exception If the API request fails or returns an error.
     */
    public function getWeather(string $city): array
    {
        // Retrieve the OpenWeather API key from the configuration
        $apiKey = config('services.openweather.key');

        // Construct the API endpoint URL
        $url = 'https://api.openweathermap.org/data/2.5/forecast';

        // Make the GET request to the OpenWeather API
        $response = Http::get($url, [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric', // Use 'metric' for Celsius
        ]);

        // Check if the response indicates a failure
        if ($response->failed()) {
            // Log the error for debugging purposes
            \Log::error('OpenWeather API error:', [
                'city' => $city,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Throw an exception with the error message
            throw new \Exception('OpenWeather API error: ' . $response->body());
        }

        // Return the JSON-decoded response data
        return $response->json();
    }
}
