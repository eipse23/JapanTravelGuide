<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PlacesService
{
    /**
     * Fetch a list of places near a given city.
     *
     * @param string $city The name of the city to search for places.
     * @return array The list of places as an associative array.
     * @throws \Exception If the API request fails or returns an error.
     */
    public function getPlaces(string $city): array
    {
        // Retrieve the Foursquare API key from the configuration
        $apiKey = config('services.foursquare.key');

        // Construct the API endpoint URL
        $url = 'https://api.foursquare.com/v3/places/search';

        // Make the GET request to the Foursquare API with the necessary headers and parameters
        $response = Http::withHeaders([
            'Authorization' => $apiKey,
        ])->get($url, [
            'near' => $city,
            'limit' => 20, // Limit the number of results to 20
        ]);

        // Check if the response indicates a failure
        if ($response->failed()) {
            // Log the error for debugging purposes
            \Log::error('Foursquare API error:', [
                'city' => $city,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Throw an exception with the error message
            throw new \Exception('Foursquare API error: ' . $response->body());
        }

        // Return the JSON-decoded response data
        return $response->json();
    }
}
