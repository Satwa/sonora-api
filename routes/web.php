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

$router->get('/', function () use ($router) {
    // $results = DB::select("SELECT * FROM songs");

    return response(["success" => true, "status" => 200]);
});

$router->get('/songs', function () use ($router) {
    $results = DB::select("SELECT performer, song, song_id FROM songs");

    // foreach ($results as $song) {
    //     $song->song_id = slugify(preg_replace('/(?<!\ )[A-Z]/', ' $0', $song->song_id));
    // }

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



// $router->get('/debug', function() use ($router){
//     $results = DB::select('SELECT * FROM songs_data');

//     foreach($results as $sdata){ // normalize slug
//         $updated = [
//             ":song_id" => slugify(preg_replace('/(?<!\ )[A-Z]/', ' $0', $sdata->song_id)),
//             ":id"      => $sdata->id
//         ];
//         DB::update("UPDATE songs_data SET song_id = :song_id WHERE id = :id", $updated);
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