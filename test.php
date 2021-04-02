<?php

    require __DIR__ . "/vendor/autoload.php";

    $city = "moskva";
    $query = "гитара";
    $category = "muzykalnye_instrumenty";
    $page = 2;

    $client = new GuzzleHttp\Client([
        'base_uri' => 'https://avito.ru'
    ]);

    $response = $client->request('GET', "/$city/$category", [
        'query' => "q=$query&p=$page"
    ]); 

    

    dump($response); 


