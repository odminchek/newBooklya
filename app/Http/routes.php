<?php

use Illuminate\Http\Response;

// Это было из коробки, для главной страницы
Route::get( '/', function () {
    return view( 'welcome' );
});

Route::get( '/api/categories/all', 'ApiController@categoriesGetAll' );  
Route::get( '/api/categories/webinars', 'ApiController@webinarsFromCategory' );  // ?alias=somethink
Route::get( '/api/webinar/one', 'ApiController@webinarWithSubjAndSubjCat' );  // ?alias=somethink
Route::get( '/api/categories/experts', 'ApiController@getExperts' );  // ?alias=somethink
Route::get( '/api/article/profile', 'ApiController@getArticlesByUser' );  // ?id=mongo_id
Route::get( '/api/feedbacks/profile', 'ApiController@getFeedbacksForUser' );  // ?id=mongo_id
Route::get( '/api/article/category', 'ApiController@getArticlesFromCategory' );  // ?alias=somethink
Route::get( '/api/article/one', 'ApiController@getArticleByAlias' );  // ?alias=somethink

// Route::post( '/api/auth/signin', 'ApiController@userSignIn' );

// Route::group( [ 'middleware' => 'cors' ], function( Router $router )
// {
//     $router->get( '/api/auth/signin', 'ApiController@userSignIn' );
// } );

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/auth/signin', 'ApiController@userSignIn' );
// } );


Route::get( '/api/categories/subjects', 'ApiController@getSubjectsForCategory' );  // ?id=mongo_id