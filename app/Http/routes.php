<?php

use Illuminate\Http\Response;

//
Route::get( '/', function () {
    return view( 'welcome' );
});

// все категории
// Route::get( '/api/categories/all', function () {
    // return 'All categoies';
// } );
Route::get('/api/categories/all', 'SubjectCategoryController@apiGetAll');  

// массив вебинаров указанной категории
// Route::get( '/api/categories/webinars', function () {
// 	// /api/categories/webinars?alias=somethink
//     return 'Webinars from category';
// } );

// один вебинар
Route::get( '/api/webinar/one', function () {
	// /api/webinar/one?alias=somethink
    return 'One webinar';
} );






