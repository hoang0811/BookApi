<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
//    $targetFolder = base_path().'/storage/app/public'; 
//    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/storage'; 
//    symlink($targetFolder, $linkFolder); 
    return view('welcome');
});
