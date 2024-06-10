<?php

namespace App\Services;

use App\Interfaces\BinServiceInterface;

class BinService implements BinServiceInterface {
    private $baseUrl;

    public function __construct() {
        $this->baseUrl = $_ENV['BINLIST_URL'] ?? 'https://lookup.binlist.net/';
    }

    public function lookup($bin) {
        $url = $this->baseUrl . $bin;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            throw new \Exception("Error fetching BIN results.");
        }
            
        // $response = '{"number":{},"scheme":"visa","type":"debit","brand":"Visa Classic","country":{"numeric":"208","alpha2":"DK","name":"Denmark","emoji":"🇩🇰","currency":"DKK","latitude":56,"longitude":
        //     10},"bank":{"name":"Jyske Bank A/S"}}';
        return json_decode($response, true);
    }
}
