<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function index()
    {
        // Your Newsdata.io API key
        $apiKey = 'pub_6352813b93c086b17c8e10b8cf1924b21507e';
        
        // API endpoint with query
        $url = "https://newsdata.io/api/1/news?apikey={$apiKey}&q=pemphigus&language=en";

        // Make HTTP GET request using Laravel's HTTP client
        $response = Http::get($url);

        if ($response->successful()) {
            // Decode JSON response
            $newsData = $response->json();

            // Pass data to view (or return as JSON for now)
            // return $newsData; // Option 1: return JSON
            return view('news.index', ['news' => $newsData['results']]); // Option 2: pass to view
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch news'
            ]);
        }
    }
}
