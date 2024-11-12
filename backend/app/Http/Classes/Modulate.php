<?php

namespace App\Http\Classes;

use Illuminate\Support\Facades\URL;

abstract class Modulate
{
    /**
     * Verification Links template
     *
     * @param String $route, url from application
     * @param Array $params, implents parameter to URL
     * @return String
     */
    static public function signedLink(String $route, Array $params = null): String
    {
        // BaseURL (SERVER)
        $assignedBaseURL = URL::to('/') . '/api';
        
        // External URL (APP)
        $appBaseURL = env('CLIENT_URL');

        // Create Signed Laravel Route
        $signedVerificationLink = URL::temporarySignedRoute(
            $route,
            now()->addMinutes(10800),       // 1 Week
            $params, 
        );

        // Modulate VerificationLink for SPA URL (Client)
        // Replace $assignedBaseURL from $sigendVerificationLink by new $appBaseURL
        return str_replace($assignedBaseURL, $appBaseURL, $signedVerificationLink);
    }

    /**
     * Sanitize Links
     *
     * @param string $rawPath
     * @return string|null
     */
    static public function sanitizeLink(string $rawPath = ''): string|null
    {
        $string = null;
        if(!$rawPath) return null;
        if(
            strpos($rawPath, 'http://') !== false 
            || strpos($rawPath, 'https://') !== false 
            || strpos($rawPath, 'www.') !== false
        ) {
            // Manipulate
            // Remove characters before https, http and www.
            // Set to 'www.' + $string
            // Remove all special characters
            $string = preg_replace('/^.*?(https?:\/\/|www\.)/i', '', $rawPath);
            $string = str_replace(array('http://','https://', 'www.', 'http://www.', 'https://www.'), '', $string);
            $string = 'www.' . $string;
            $string =  preg_replace("#[^a-zA-Z0-9_\-./:@%+~=?&\#]#", "", $string);   
        }

        return $string;
    }
}
