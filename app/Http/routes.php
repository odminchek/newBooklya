<?php

use Illuminate\Http\Response;

// Это было из коробки, для главной страницы
Route::get( '/', function () {
    return view( 'welcome' );
});

Route::get('/api/categories/all', 'ApiController@categoriesGetAll');  
Route::get('/api/categories/webinars', 'ApiController@webinarsFromCategory');  // ?alias=somethink