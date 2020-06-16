<?php

$router->get('/', function () use ($router) {
    $results = DB::select("SELECT * FROM songs_data");
    return response(json_encode($results))
                ->header('Content-Type', 'application/json');
});
