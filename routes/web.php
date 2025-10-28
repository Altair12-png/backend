<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/proxy-image/{filename}', function ($filename) {
    $path = storage_path('app/public/fasilitas/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }

    $mime = mime_content_type($path);
    return Response::make(file_get_contents($path), 200, [
        'Content-Type' => $mime,
        'Access-Control-Allow-Origin' => '*',
    ]);
});
