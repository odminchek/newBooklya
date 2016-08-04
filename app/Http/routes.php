<?php

use Illuminate\Http\Response;

// Это было из коробки, для главной страницы
Route::get( '/', function () {
    return view( 'welcome' );
});

// Это работает для первого роута, но сделаем более правильно
Route::get('/api/categories/all', 'ApiController@categoriesGetAll');  
