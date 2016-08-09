<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\SubjectCategoryModel;
use App\SubjectModel;
use App\WebinarModel;
use App\UserModel;
use App\ArticleModel;
use App\FeedbackModel;

class ApiController extends Controller
{
    private $myLogging = TRUE;
    private $logFile = '/var/www/booklya/booklya.log';

    public function categoriesGetAll()
    {
    	// Получаем список категорий, проверяем корректность
    	if ( !$result = SubjectCategoryModel::all()->toArray() OR !is_array( $result ) OR empty( $result ) ):
    		// Если не получили, пишем лог (пока не работает)
    		$this->log( 'categoriesGetAll: Список не получен!' );
    		// Возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// Если всё хорошо, кодируем в JSON и отдаём
    	return json_encode( $result );
    }

    public function webinarsFromCategory( Request $request )
    {
    	// проверяем передан ли alias и корректно ли передан
    	if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
    		OR !is_string( $alias ) 
    		OR empty( $alias ) 
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Некорректный alias!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// находим и проверяем корректность получения категории с этим алиасом
    	if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->get()
    		OR !$subjectCategory = $subjectCategory->toArray()
    		OR empty( $subjectCategory )
    		OR !$subjectCategory = $subjectCategory[ 0 ]
    		OR !is_array( $subjectCategory )
    		OR empty( $subjectCategory )
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Не получена категория с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// находим и проверяем корректность получения всех тем, входящих в данную категорию
    	if( !isset( $subjectCategory[ '_id' ] )
    		OR !$subjects = SubjectModel::where( 'subjectCategory', '=', $subjectCategory[ '_id' ] )->get()
    		OR !$subjects = $subjects->toArray()
    		OR !is_array( $subjects )
    		OR empty( $subjects )
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Не получены темы, входящие в категорию с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// Получаем ID всех тем, входящих в данную категорию
    	foreach( $subjects as $subject ):
    		// Проверяем корректность значения
    		if( !is_array( $subject)
    			OR !isset( $subject[ '_id' ] )
    			OR !is_string( $subject[ '_id' ] )
    			OR empty( $subject[ '_id' ] )
    			):
    			// переходим к следующему
    			continue;
    		endif;
    		// Добавляем ID темы в массив
    		$subjectIds[] = $subject[ '_id' ];
    	endforeach;

    	// Проверяем массив с ID тем
    	if( !is_array( $subjectIds )
    		OR empty( $subjectIds )
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Не получены ID тем, входящих в категорию с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// Получаем вебинары и проверяем корректность получения
    	if( !$webinars = WebinarModel::whereIn( 'subject', $subjectIds )->get() 
    		OR !$webinars = $webinars->toArray()
    		OR empty( $webinars )
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Не получены вебинары категории с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// помещаем вебинары в категорию
    	$subjectCategory[ 'webinars' ] = $webinars;

    	// возвращаем
    	return json_encode( $subjectCategory );
    }

    public function webinarWithSubjAndSubjCat( Request $request )
    {
        // проверяем передан ли alias и корректно ли передан
        if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
            OR !is_string( $alias ) 
            OR empty( $alias ) 
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Некорректный alias!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем вебинар и проверяем
        if( !$webinar = WebinarModel::where( 'seo.alias', '=', '/' . $alias )->get()
            OR !$webinar = $webinar->toArray()
            OR !isset( $webinar[ 0 ] )
            OR !is_array( $webinar[ 0 ] )
            OR empty( $webinar[ 0 ] )
            OR !$webinar = $webinar[ 0 ]
            OR !isset( $webinar[ 'subject' ] )
            OR empty( $webinar[ 'subject' ] )
            OR !ctype_xdigit( $webinar[ 'subject' ] )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получен вебинар с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем subject
        // из-за косяков с работой find() пока сделаем через задницу - получим все subject и выберем нужный в коде
        if( !$subjects = SubjectModel::all()
            OR !$subjects = $subjects->toArray()
            OR !is_array( $subjects )
            OR empty( $subjects )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получен список subjects!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // выбираем тему с нужным нам _id
        foreach( $subjects as $subject ):
            if( isset( $subject[ '_id' ] ) 
                AND $subject[ '_id' ] === $webinar[ 'subject' ]
                ):
                // переносим тему в вебинар
                $webinar[ 'subject-body' ] = $subject;
                break;
            endif;
        endforeach;

        // больше не нужна
        if( isset( $subjects ) ):
            unset( $subjects );
        endif;

        // проверяем subject
        if( !isset( $webinar[ 'subject-body' ] )
            OR !is_array( $webinar[ 'subject-body' ] )
            OR empty( $webinar[ 'subject-body' ] )
            OR !isset( $webinar[ 'subject-body' ][ 'subjectCategory' ] )
            OR !is_string( $webinar[ 'subject-body' ][ 'subjectCategory' ] )
            OR empty( $webinar[ 'subject-body' ][ 'subjectCategory' ] )
            OR !ctype_xdigit( $webinar[ 'subject-body' ][ 'subjectCategory' ] )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не найдена категория для вебинара с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем subjectCategory и проверяем
        if( !$subjectCategories = SubjectCategoryModel::all()
            OR !$subjectCategories = $subjectCategories->toArray()
            OR !is_array( $subjectCategories )
            OR empty( $subjectCategories )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получен список subjectCategory!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // выбираем subjectCategory с нужным _id
        foreach( $subjectCategories as $subjectCategory ):
            if( isset( $subjectCategory[ '_id' ] )
                AND $subjectCategory[ '_id' ] === $webinar[ 'subject-body' ][ 'subjectCategory' ]
                ):
                // переносим subjectCategory в вебинар
                $webinar[ 'subjectCategory-body' ] = $subjectCategory;
                break;
            endif;
        endforeach;

        // больше не нужна
        if( isset( $subjectCategories ) ):
            unset( $subjectCategories );
        endif;

        // проверяем корректность subjectCategory
        if( !isset( $webinar[ 'subjectCategory-body' ] )
            OR !is_array( $webinar[ 'subjectCategory-body' ] )
            OR empty( $webinar[ 'subjectCategory-body' ] )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получена subjectCategory для вебинара с алисаом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // $subjectCategory

        // echo '<pre>';
        // var_dump( $webinar );
        // echo '</pre>';
        // return;

        return json_encode( $webinar );
    }

    public function getExperts( Request $request )
    {
    	// проверяем передан ли alias и корректно ли передан
    	if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
    		OR !is_string( $alias ) 
    		OR empty( $alias ) 
    		):
    		// пишем лог
    		$this->log( 'getExperts: Некорректный alias!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// получаем категорию по алиасу и проверяем корректность получения
    	if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->get()
    		OR !$subjectCategory = $subjectCategory->toArray() 
    		OR !is_array( $subjectCategory )
    		OR empty( $subjectCategory )
    		OR !isset( $subjectCategory[ 0 ] )
    		OR !$subjectCategory = $subjectCategory[ 0 ]
    		OR !is_array( $subjectCategory )
    		OR empty( $subjectCategory )
    		OR !isset( $subjectCategory[ "_id" ] )
    		OR !$subjectCategoryId = $subjectCategory[ "_id" ]
    		OR !is_string( $subjectCategoryId )
    		OR empty( $subjectCategoryId )
    		):
    		// пишем лог
    		$this->log( 'getExperts: Не получена subjectCategory!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// получаем экспертов этой категории и проверяем корректность получения
    	if( !$experts = UserModel::where( 'userRoles', 'All', [ 'teacher' ] )->where( 'subjectsCategory', 'All', [ $subjectCategoryId ] )->get()
    		OR !$experts = $experts->toArray()
    		OR !is_array( $experts )
    		OR empty( $experts )
    		):
    		// пишем лог
    		$this->log( 'getExperts: Не получены эксперты для категории ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// если всё ок, засовываем в категорию её экспертов
    	$subjectCategory[ 'experts' ] = $experts;

    	// преобразуем в JSON и возвращаем
    	return json_encode( $subjectCategory );
    }

    public function getOneProfile( Request $request )
    {
        // проверяем передан ли id и корректно ли передан
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )  
            OR !is_string( $id ) 
            OR empty( $id ) 
            OR !ctype_xdigit( $id )
            ):
            // пишем лог
            $this->log( 'getOneProfile: Некорректный id! [' . $id . ']' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем юзеров
        if( !$users = UserModel::all()
            OR !$users = $users->toArray()
            OR !is_array( $users )
            OR empty( $users )
            ):
            // пишем лог
            $this->log( 'getOneProfile: Не получен список пользователей!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // ищем нужного
        foreach( $users as $user ):
            if( isset( $user[ '_id' ] )
                AND $user[ '_id' ] === $id
                ):
                $currentUser = $user;
                break;
            endif;
        endforeach;

        // clean
        if( isset( $users ) ):
            unset( $users );
        endif;

        // проверяем
        if( !isset( $currentUser ) 
            OR !is_array( $currentUser )
            OR empty( $currentUser )
            ):
            // пишем лог
            $this->log( 'getOneProfile: Не найден пользователь с _id=' . $id . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        return json_encode( $currentUser );
    }

    public function getArticlesFromCategory( Request $request )
    {
        // проверяем передан ли alias и корректно ли передан
        if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
            OR !is_string( $alias ) 
            OR empty( $alias ) 
            ):
            // пишем лог
            $this->log( 'getArticlesFromCategory: Некорректный alias!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем категорию по алиасу и проверяем корректность получения
        if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->get()
            OR !$subjectCategory = $subjectCategory->toArray() 
            OR !is_array( $subjectCategory )
            OR empty( $subjectCategory )
            OR !isset( $subjectCategory[ 0 ] )
            OR !$subjectCategory = $subjectCategory[ 0 ]
            OR !is_array( $subjectCategory )
            OR empty( $subjectCategory )
            OR !isset( $subjectCategory[ "_id" ] )
            OR !$subjectCategoryId = $subjectCategory[ "_id" ]
            OR !is_string( $subjectCategoryId )
            OR empty( $subjectCategoryId )
            ):
            // пишем лог
            $this->log( 'getArticlesFromCategory: Не получена subjectCategory!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем статьи, относящиеся к данной категории и првоеряем корректность полученных данных
        if( !$articles = ArticleModel::where( 'subjectCategory', '=', $subjectCategoryId )->get()
            OR !$articles = $articles->toArray()
            OR !is_array( $articles )
            OR empty( $articles )
            ):
            // пишем лог
            $this->log( 'getArticlesFromCategory: Не список статей для категории с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        return json_encode( $articles );
    }

    public function getArticleByAlias( Request $request )
    {
        // проверяем передан ли alias и корректно ли передан
        if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
            OR !is_string( $alias ) 
            OR empty( $alias ) 
            ):
            // пишем лог
            $this->log( 'getArticleByAlias: Некорректный alias!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // пробуем получить статью по алиасу и проверяем корректность получения
        $alias = '/' . $alias;
        if( !$article = ArticleModel::where( 'seo.alias', '=', $alias )->get()
            OR !$article = $article->toArray()
            OR !isset( $article[ 0 ] )
            OR !$article = $article[ 0 ]
            OR !is_array( $article )
            OR empty( $article )
            ):
            // пишем лог
            $this->log( 'getArticleByAlias: Статья с алиасом ' . $alias . ' не получена!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        return json_encode( $article );
    }

    public function getArticlesByUser( Request $request )
    {
        // проверяем передан ли id и корректно ли передан
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )  
            OR !is_string( $id ) 
            OR empty( $id )
            OR mb_strlen( $id ) != 24
            OR !ctype_xdigit( $id )
            ):
             // пишем лог
            $this->log( 'getArticlesByUser: Некорректный id юзера!' );
             // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем статьи, соотв. этому юзеру, и проверяем
        if( !$articles = ArticleModel::where( 'user', '=', $id )->get()
            OR !$articles = $articles->toArray()
            OR !is_array( $articles )
            OR empty( $articles )
            ):
            $this->log( 'getArticlesByUser: Не получены статьи юзера с id ' . $id );
            return json_encode( array() );
        endif;

        return json_encode( $articles );
    }

    public function getFeedbacksForUser( Request $request )
    {
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )  
            OR !is_string( $id ) 
            OR empty( $id )
            OR mb_strlen( $id ) != 24
            OR !ctype_xdigit( $id )
            ):
             // пишем лог
            $this->log( 'getFeedbacksForUser: Некорректный id юзера!' );
             // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем отзывы, соотв. этому юзеру, и проверяем
        if( !$feedbacks = FeedbackModel::where( 'userWhom', '=', $id )->get()
            OR !$feedbacks = $feedbacks->toArray()
            OR !is_array( $feedbacks )
            OR empty( $feedbacks )
            ):
            $this->log( 'getFeedbacksForUser: Не получены отзывы юзера с id ' . $id );
            return json_encode( array() );
        endif;

        return json_encode( $feedbacks );
    }

    public function userSignIn( Request $request )
    {
        // получаем логин и пароль из запроса
        if( !$username = strip_tags( stripslashes( trim( $request->input( 'username' ) ) ) )
            OR !$password = strip_tags( stripslashes( trim( $request->input( 'password' ) ) ) )
            OR !is_string( $username )
            OR !is_string( $password )
            OR !mb_strlen( $username )
            OR !mb_strlen( $password )
            ):
            $this->log( 'userSignIn: Учётные данные некорректны!' );
            return json_encode( array() );
        endif;

        // пытаемся найти такого юзера
        if( !$user = UserModel::where( 'phoneNumber', '=', $username )->orWhere( 'primaryEmail', '=', $username )->get()
            OR !$user = $user->toArray()
            OR !is_array( $user )
            OR !isset( $user[ 0 ] )
            OR !$user = $user[ 0 ]
            OR !is_array( $user )
            ):
            $this->log( 'userSignIn: Не найден пользователь с username = ' . $username . '!' );
            return json_encode( array() );
        endif; 

        // проверяем пароль
        if( !isset( $user[ 'password' ] ) 
            OR !is_string( $user[ 'password' ] )
            OR !mb_strlen( $user[ 'password' ] )
            OR $user[ 'password' ] !== md5( $password )
            ):
            $this->log( 'userSignIn: Неверный пароль для пользователя с username = ' . $username . '!' );
            return json_encode( array() );
        endif;

        // всё ОК, юзер есть, пароль подходит
        // echo 'Success!';
        echo '<pre>';
        var_dump( $request->session()->all() );
        echo '</pre>';
        // echo '<pre>';
        // var_dump( $request->input( 'username' ) );
        // var_dump( $request->input( 'password' ) );
        // echo '</pre>';
        return;
    }

    public function getSubjectsForCategory( Request $request )
    {
        // проверяем передан ли id и корректно ли передан
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )  
            OR !is_string( $id ) 
            OR empty( $id ) 
            OR !ctype_xdigit( $id )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Некорректный id!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем категории
        if( !$subjectCategories = SubjectCategoryModel::all()
            OR !$subjectCategories = $subjectCategories->toArray()
            OR !is_array( $subjectCategories )
            OR empty( $subjectCategories )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Не получены subjectCategory!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // перебираем категории, ищем нужную
        foreach( $subjectCategories as $subjectCategory ):
            if( isset( $subjectCategory[ '_id' ] ) 
                AND $subjectCategory[ '_id' ] === $id
                ):
                $category = $subjectCategory;
                break;
            endif;
        endforeach;

        // больше не нужна
        if( isset( $subjectCategories ) ):
            unset( $subjectCategories );
        endif;

        // проверяем subjectCategory
        if( !isset( $category )
            OR !is_array( $category )
            OR empty( $category )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Не получена subjectCategory с _id=' . $id . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем темы
        if( !$subjects = SubjectModel::all()
            OR !$subjects = $subjects->toArray()
            OR !is_array( $subjects )
            OR empty( $subjects )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Не получены subjects!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // ищем темы, которые входят в указанную категорию
        foreach( $subjects as $subject ):
            if( isset( $subject[ 'subjectCategory' ] )
                AND $subject[ 'subjectCategory' ] === $category[ '_id' ]
                ):
                $matchSubjects[] = $subject;
            endif;
        endforeach;

        // больше не нужна
        if( isset( $subjects ) ):
            unset( $subjects );
        endif;

        // проверяем темы
        if( !isset( $matchSubjects )
            OR !is_array( $matchSubjects )
            OR empty( $matchSubjects )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Не найдены subjects для subjectCategory с _id=' . $id . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        $category[ 'subjects' ] = $matchSubjects;

        return json_encode( $category );
    }

    private function log( $msg )
    {
        
        if( $this->myLogging ):
            date_default_timezone_set( 'Europe/Samara' );
            $timestamp = date( "Y.m.d H:i:s" ); 
            file_put_contents( $this->logFile, $timestamp . ' ' . $msg . "\n", FILE_APPEND );
        else:
            Log::error( $msg );
        endif;
    }
}
