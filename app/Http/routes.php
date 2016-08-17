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
//	{ username, password }
// Route::post( '/api/auth/signin', 		'ApiController@userSignIn' );

// Добавление отзыва о пользователе
//	{ user_id, auth_key, for_user_id, text }
// Route::post( '/api/feedbacks/create', 	'ApiController@createFeedback' );

// Отправка сообщения от пользователя пользователю
//	{ user_id, auth_key, interlocutor, text }
// Route::post( '/api/messages/create', 	'ApiController@createMessage' );

// Создание урока
//	{ user_id, auth_key, start_date, stop_date, price, subject, theme }
// Route::post( '/api/lessons/create', 	'ApiController@createLesson' );

// Создание статьи
// 	{ user_id, auth_key, title, subject, subjectCategory, image, text, alias }
// Route::post( '/api/article/create', 	'ApiController@createArticle' )->middleware( [ 'json' ] );

// Нам везде приходит JSON, значит, уместно сделать группу роутов с общим JsonMiddleware
Route::group
(
	[ 'middleware' => 'json' ],
	function()
	{
		// авторизация
		Route::post( '/api/auth/signin', 		'ApiController@userSignIn' );

		// а тут нам надо ещё проверить был ли юзер авторизован
		Route::group
		(
			[ 'middleware' => 'apiauth' ],
			function()
			{
				Route::post( '/api/feedbacks/create', 	'ApiController@createFeedback' );
				Route::post( '/api/messages/create', 	'ApiController@createMessage' );
				Route::post( '/api/lessons/create', 	'ApiController@createLesson' );
				Route::post( '/api/article/create', 	'ApiController@createArticle' );
			}
		);
	}
);

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


