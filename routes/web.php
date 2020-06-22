<?php

// from https://kodex.pierros.fr/php/url-php-slug/
function slugify($string, $delimiter = '-') {
    $oldLocale = setlocale(LC_ALL, '0');
    setlocale(LC_ALL, 'en_US.UTF-8');
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower($clean);
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    $clean = trim($clean, $delimiter);
    setlocale(LC_ALL, $oldLocale);
    return $clean;
}

/**
 * SONGS ENDPOINTS
 */

$router->get('/', function () use ($router) {
    return response(["success" => true, "status" => 200]);
});

$router->get('/songs', function () use ($router) {
    $results = DB::select("SELECT performer, song, song_id FROM songs GROUP BY song_id");

    return response($results);
});

$router->get('/songs/{id}', function ($id) use ($router) {
    $song = DB::select("SELECT songs.*, songs_data.* FROM songs LEFT JOIN songs_data ON songs.song_id = songs_data.song_id WHERE songs.song_id = ? LIMIT 1", [$id]);
    
    if(!$song){
        return response(["success" => false, "status" => 404]);
    }

    return response($song);
});

$router->get('/songs/search/{query}', function ($query) use ($router) {
    $query = "%$query%";
    $songs = DB::select("SELECT performer, song, song_id FROM songs WHERE performer LIKE :q OR song LIKE :q GROUP BY song_id", [":q" => $query]);
    
    if(!$songs){
        return response(["success" => false, "status" => 404]);
    }

    return response($songs);
});



/**
 * GENRES ENDPOINTS
 */

$router->get('/genres', function () use ($router) {
    $req = DB::select("SELECT genre FROM songs_data GROUP BY song_id");
    $genres = [];
    foreach ($req as $genre) {
        if(strlen($genre->genre) > 0){
            $genres = array_merge($genres, json_decode($genre->genre));
        }
    }

    return response($genres);
});

$router->get('/genres/{genre}/songs', function ($genre) use ($router) { // genres better be sanitized
    $genre = str_replace('%20', ' ', $genre);
    $req = DB::select("SELECT songs.*, songs_data.* FROM songs LEFT JOIN songs_data ON songs.song_id = songs_data.song_id WHERE songs_data.genre LIKE ?", ["%\"$genre\"%"]);

    return response($req);
});

$router->get('/genres/search/{query}', function ($query) use ($router) { // genres better be sanitized
    $genre = str_replace('%20', ' ', $query);
    
    $req = DB::select("SELECT genre FROM songs_data WHERE genre LIKE ? GROUP BY song_id ", ["%$genre%"]);
    $genres = [];
    foreach ($req as $genre) {
        if(strlen($genre->genre) > 0){
            $genres = array_merge($genres, json_decode($genre->genre));
        }
    }
    $genres = array_unique($genres);
    $genres = array_filter($genres, function($value) use ($query) {
                  return strpos($value, $query) !== false;
              });

    return response($genres);
});

/**
 * BPM ENDPOINT
 */

$router->get('/bpm/{value}/songs', function ($value) use ($router) { // genres better be sanitized
    $req = DB::select("SELECT performer, song, song_id FROM songs_data WHERE tempo >= ? AND tempo <= ?", [(int)$value - 5, (int)$value + 5]);

    return response($req);
});


/**
 * AVERAGE ENDPOINT
 */


$router->get('/average', function () use ($router) { // genres better be sanitized
    $req = DB::select("SELECT * FROM songs_data GROUP BY song_id");

    $sum = [
        "duration"          => 0,
        "danceability"      => 0,
        "energy"            => 0,
        "key"               => [],
        "loudness"          => 0,
        "speechiness"       => 0,
        "acousticness"      => 0,
        "instrumentalness"  => 0,
        "liveness"          => 0,
        "valence"           => 0,
        "tempo"             => 0,
    ];

    foreach ($req as $song) {
        $sum['duration']     += (int) $song->spotify_track_duration_ms;
        $sum['danceability'] += (float) $song->danceability;
        $sum['key'][] = (int) $song->key;
        $sum['energy']       += (float) $song->energy;
        $sum['loudness']     += (float) $song->loudness;
        $sum['speechiness']  += (float) $song->speechiness;
        $sum['acousticness'] += (float) $song->acousticness;
        $sum['instrumentalness'] += (float) $song->instrumentalness;
        $sum['liveness']     += (float) $song->liveness;
        $sum['valence']      += (float) $song->valence;
        $sum['tempo']        += (int) $song->tempo;
    }

    $sum['duration']     = $sum['duration']     / sizeof($req);
    $sum['danceability'] = $sum['danceability'] / sizeof($req);
    $sum['energy']       = $sum['energy']       / sizeof($req);
    $sum['loudness']     = $sum['loudness']     / sizeof($req);
    $sum['speechiness']  = $sum['speechiness']  / sizeof($req);
    $sum['acousticness'] = $sum['acousticness'] / sizeof($req);
    $sum['instrumentalness'] = $sum['instrumentalness'] / sizeof($req);
    $sum['liveness']     = $sum['liveness']     / sizeof($req);
    $sum['valence']      = $sum['valence']      / sizeof($req);
    $sum['tempo']        = round($sum['tempo'] / sizeof($req), 0, PHP_ROUND_HALF_UP);

    // Most used key, integer to pitch class from https://en.wikipedia.org/wiki/Pitch_class 
    $count = array_count_values($sum['key']); // Counts the values in the array, returns associatve array
    arsort($count); // Sort it from highest to lowest
    $keys = array_keys($count); // Split the array so we can find the most occuring key
    $sum['key'] = $keys[0];
    
    return response($sum);
}); 



// $router->get('/debug', function() use ($router){
//     $results = DB::select('SELECT tempo, song_id FROM songs_data');

//     foreach($results as $sdata){ // normalize slug
//         $updated = [
//             ":id" => $sdata->song_id,
//             ":tempo"   => round($sdata->tempo, 0, PHP_ROUND_HALF_UP)
//         ];
//         DB::update("UPDATE songs_data SET tempo = :tempo WHERE song_id = :id", $updated);
//     }
// });


/*
Clear backslashes from database

foreach($results as $sdata){
    foreach($sdata as $key => $value){
        $sdata->{$key} = str_replace("\\", "" , $value);
    }
    echo '<pre>';
    print_r($sdata);
    echo '</pre>';
    $updated = [
        "song_id"             => $sdata->song_id,
        "performer"           => $sdata->performer,
        "song"                => $sdata->song,
        "genre"               => $sdata->genre,
        "spotify_track_album" => $sdata->spotify_track_album,
        "id"                  => $sdata->id,
    ];
    DB::update("UPDATE songs_data SET song_id = :song_id, performer = :performer, song = :song, genre = :genre, spotify_track_album = :spotify_track_album WHERE id = :id", $updated);
}

foreach($results as $sdata){
    foreach($sdata as $key => $value){
        $sdata->{$key} = str_replace("\\", "" , $value);
    }
    echo '<pre>';
    print_r($sdata);
    echo '</pre>';
    $updated = [
        "song_id"             => $sdata->song_id,
        "performer"           => $sdata->performer,
        "song"                => $sdata->song,
        "id"                  => $sdata->id,
    ];
    DB::update("UPDATE songs SET song_id = :song_id, performer = :performer, song = :song WHERE id = :id", $updated);
}
*/