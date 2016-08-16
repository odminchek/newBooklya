<?php

use Illuminate\Http\Response;

// Это было из коробки, для главной страницы
Route::get( '/', function () {
    return view( 'welcome' );
});

/**
	GET-запросы
*/

// Получаем список subjectCategory
Route::get( '/api/categories/all', 		'ApiController@categoriesGetAll' );  

// Получаем описание subjectCategory и массив вебинаров этой категории
Route::get( '/api/categories/webinars', 'ApiController@webinarsFromCategory' );  		// ?alias=somethink

// Получаем объект webinar с вложенными объектами subject и subjectCategory
Route::get( '/api/webinar/one', 		'ApiController@webinarWithSubjAndSubjCat' );  	// ?alias=somethink

// Получаем экспертов указанной subjectCategory
Route::get( '/api/categories/experts', 	'ApiController@getExperts' );  					// ?alias=somethink

// Получаем профиль одного эксперта
Route::get( '/api/profile/one', 		'ApiController@getOneProfile' );  				// ?id=mongo_id

// Получаем все статьи указанного юзера
Route::get( '/api/article/profile', 	'ApiController@getArticlesByUser' );  			// ?id=mongo_id

// Получаем список всех отзывов о пользователе
Route::get( '/api/feedbacks/profile', 	'ApiController@getFeedbacksForUser' );  		// ?id=mongo_id

// Получаем список статей указанной subjectCategory
Route::get( '/api/article/category', 	'ApiController@getArticlesFromCategory' );  	// ?alias=somethink

// Получаем статью по её алиасу
Route::get( '/api/article/one', 		'ApiController@getArticleByAlias' );  			// ?alias=somethink

// Получаем список subjects, принадлежащих указанной subjectCategory
Route::get( '/api/categories/subjects', 'ApiController@getSubjectsForCategory' );  		// ?id=mongo_id



/**
	POST-запросы
*/

// Авторизация. Получаем auth_key для POST-запросов от пользователя
Route::post( '/api/auth/signin', 		'ApiController@userSignIn' ); 		//	{ username, password }

// Добавление отзыва о пользователе
Route::post( '/api/feedbacks/create', 	'ApiController@createFeedback' );	//	{ user_id, auth_key, for_user_id, text }

// Отправка сообщения от пользователя пользователю
Route::post( '/api/messages/create', 	'ApiController@createMessage' );	//	{ user_id, auth_key, interlocutor, text }

// Создание урока
Route::post( '/api/lessons/create', 	'ApiController@createLesson' );		//	{ user_id, auth_key, start_date, stop_date, price, subject, theme }

// Создание статьи
Route::post( '/api/article/create', 	'ApiController@createArticle' );	// 	{ user_id, auth_key, title, subject, subjectCategory, image, text, alias }



/**
	POST-запросы с CORS (пока нерабочий вариант)
**/

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/auth/signin', 'ApiController@userSignIn' );
// } );

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/feedbacks/create', 'ApiController@createFeedback' );
// } );

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/messages/create', 'ApiController@createMessage' );
// } );

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/lessons/create', 'ApiController@createLesson' );
// } );

// Route::group( [ 'middleware' => 'cors' ], function()
// {
//     Route::post( '/api/article/create', 'ApiController@createArticle' );
// } );


