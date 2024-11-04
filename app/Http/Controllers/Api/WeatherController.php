<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function getTokyoWeather()
    {
        $apiKey = config('services.openweather.key');
        // 東京の位置情報で固定
        $city = 'Tokyo';
        $url = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&appid={$apiKey}&units=metric&lang=ja";

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $weatherData = $response->json();

                $filteredData = collect($weatherData['list'])->map(function ($forecast) {
                    return [
                        'date' => $forecast['dt_txt'],
                        'icon' => "http://openweathermap.org/img/wn/{$forecast['weather'][0]['icon']}@2x.png",
                        'temp_max' => $forecast['main']['temp_max'],
                        'temp_min' => $forecast['main']['temp_min'],
                    ];
                });

                return response()->json(['data' => $filteredData], 200);
            } else {
                return response()->json(['message' => '天気情報の取得に失敗しました'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'エラーが発生しました', 'error' => $e->getMessage()], 500);
        }
    }
}
