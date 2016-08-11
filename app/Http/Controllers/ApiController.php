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
use App\UserAuthModel;

class ApiController extends Controller
{
    private $myLogging = TRUE;
    private $logFile = '/var/www/booklya/booklya.log';

    public function categoriesGetAll()
    {
    	// Получаем список категорий, проверяем корректность
    	if ( !$subjectCategories = SubjectCategoryModel::all()
            OR !$subjectCategories = $subjectCategories->toArray() 
            OR !is_array( $subjectCategories ) 
            OR empty( $subjectCategories ) 
            OR !$subjectCategories = $this->changeId( $subjectCategories )
            ):
    		// Если не получили, пишем лог (пока не работает)
    		$this->log( 'categoriesGetAll: Список не получен!' );
    		// Возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// Если всё хорошо, кодируем в JSON и отдаём
    	return json_encode( $subjectCategories );
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
    	if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->first()
    		OR !$subjectCategory = $subjectCategory->toArray()
    		OR !is_array( $subjectCategory )
    		OR empty( $subjectCategory )
            OR !$subjectCategory = $this->changeId( $subjectCategory )
    		):
    		// пишем лог
    		$this->log( 'webinarsFromCategory: Не получена категория с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// находим и проверяем корректность получения всех тем, входящих в данную категорию
    	if( !isset( $subjectCategory[ 'id' ] )
    		OR !$subjects = SubjectModel::where( 'subjectCategory', '=', $subjectCategory[ 'id' ] )->get( [ '_id' ] )
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
            OR !is_array( $webinars )
    		OR empty( $webinars )
            OR !$webinars = $this->changeId( $webinars )
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
        if( !$webinar = WebinarModel::where( 'seo.alias', '=', '/' . $alias )->first()
            OR !$webinar = $webinar->toArray()
            OR !is_array( $webinar )
            OR empty( $webinar )
            OR !isset( $webinar[ 'subject' ] )
            OR !$this->isMongoId( $webinar[ 'subject' ] )
            OR !$webinar = $this->changeId( $webinar )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получен вебинар с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем subject
        if( !$subject = SubjectModel::find( $webinar[ 'subject' ] )
            OR !$subject = $subject->toArray()
            OR !is_array( $subject )
            OR empty( $subject )
            OR !isset( $subject[ 'subjectCategory' ] )
            OR !$this->isMongoId( $subject[ 'subjectCategory' ] )
            OR !$subject = $this->changeId( $subject )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получена subject для вебинара с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК, заносим subject в webinar и очищаем
        $webinar[ 'subject-body' ] = $subject;
        if( isset( $subject ) ) unset( $subject );

        // получаем subjectCategory
        if( !$subjectCategory = SubjectCategoryModel::find( $webinar[ 'subject-body' ][ 'subjectCategory' ] )
            OR !$subjectCategory = $subjectCategory->toArray()
            OR !is_array( $subjectCategory )
            OR empty( $subjectCategory )
            OR !$subjectCategory = $this->changeId( $subjectCategory )
            ):
            // пишем лог
            $this->log( 'webinarWithSubjAndSubjCat: Не получена subjectCategory для вебинара с алиасом ' . $alias . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК, заносим subjectCategory в webinar и очищаем
        $webinar[ 'subjectCategory-body' ] = $subjectCategory;
        if ( isset( $subjectCategory ) ) unset( $subjectCategory );

        // возвращаем вебинар
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
    	if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->first()
    		OR !$subjectCategory = $subjectCategory->toArray() 
    		OR !is_array( $subjectCategory )
    		OR empty( $subjectCategory )
    		OR !isset( $subjectCategory[ "_id" ] )
            OR !ctype_xdigit( $subjectCategory[ "_id" ] )
            OR !$subjectCategory = $this->changeId( $subjectCategory )
    		):
    		// пишем лог
    		$this->log( 'getExperts: Не получена subjectCategory!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// получаем экспертов этой категории и проверяем корректность получения
    	if( !$experts = UserModel::where( 'userRoles', 'All', [ 'teacher' ] )->where( 'subjectsCategory', 'All', [ $subjectCategory[ "id" ] ] )->get()
    		OR !$experts = $experts->toArray()
    		OR !is_array( $experts )
    		OR empty( $experts )
            OR !$experts = $this->changeId( $experts )
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
            OR !$this->isMongoId( $id )
            ):
            // пишем лог
            $this->log( 'getOneProfile: Некорректный id! [' . $id . ']' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем юзера и проверяем корректность получения
        if( !$user = UserModel::find( $id )
            OR !$user = $user->toArray()
            OR !is_array( $user )
            OR empty( $user )
            OR !$user = $this->changeId( $user )
            ):
            $this->log( 'getOneProfile: Не найден пользователь с _id=' . $id . '!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        return json_encode( $user );
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
        if( !$subjectCategory = SubjectCategoryModel::where( 'alias', '=', $alias )->first()
            OR !$subjectCategory = $subjectCategory->toArray() 
            OR !is_array( $subjectCategory )
            OR empty( $subjectCategory )
            OR !$subjectCategory = $this->changeId( $subjectCategory )
            ):
            // пишем лог
            $this->log( 'getArticlesFromCategory: Не получена subjectCategory!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем статьи, относящиеся к данной категории и првоеряем корректность полученных данных
        if( !$articles = ArticleModel::where( 'subjectCategory', '=', $subjectCategory[ 'id' ] )->get()
            OR !$articles = $articles->toArray()
            OR !is_array( $articles )
            OR empty( $articles )
            OR !$articles = $this->changeId( $articles )
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
        if( !$article = ArticleModel::where( 'seo.alias', '=', $alias )->first()
            OR !$article = $article->toArray()
            OR !is_array( $article )
            OR empty( $article )
            OR !$article = $this->changeId( $article )
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
            OR !$this->isMongoId( $id )
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
            OR !$articles = $this->changeId( $articles )
            ):
            $this->log( 'getArticlesByUser: Не получены статьи юзера с id ' . $id );
            return json_encode( array() );
        endif;

        return json_encode( $articles );
    }

    public function getFeedbacksForUser( Request $request )
    {
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )
            OR !$this->isMongoId( $id )
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
            OR !$feedbacks = $this->changeId( $feedbacks )
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
            $response[ 'status' ] = 'error';
            return json_encode( $response );
        endif;

        // пытаемся найти такого юзера
        if( !$userModel = UserModel::where( 'phoneNumber', '=', $username )->orWhere( 'primaryEmail', '=', $username )->first()
            OR !$user = $userModel->toArray()
            OR !is_array( $user )
            OR empty( $user )
            OR !isset( $user[ '_id' ] )
            ):
            $this->log( 'userSignIn: Не найден пользователь с username = ' . $username . '!' );
            $response[ 'status' ] = 'error';
            return json_encode( $response );
        endif;

        // проверяем пароль
        if( !isset( $user[ 'password' ] ) 
            OR !is_string( $user[ 'password' ] )
            OR !mb_strlen( $user[ 'password' ] )
            OR $user[ 'password' ] !== md5( $password )
            ):
            $this->log( 'userSignIn: Неверный пароль для пользователя с username = ' . $username . '!' );
            $response[ 'status' ] = 'error';
            return json_encode( $response );
        endif;

        // кладём в ответ id юзера
        $response[ 'user_id' ] = $user[ '_id' ];
        // генерим проверочный ключ
        $userAuthKey = hash( 'sha256', time() );
        
        // если у нас юзер с таким _id уже был авторизован
        if( $userAuthModel = $this->isUserAuth( $user[ '_id' ] ) ):
            // меняем ему $userAuthKey
            $userAuthModel->authKey = $userAuthKey;
            // сохраняем в базу
            if( !$userAuthModel->save() ):
                // пишем лог
                $this->log( 'userSignIn: ошибка сохранения auth_key для юзера с id=' . $user[ '_id' ] );
                // статус - ошибка
                $response[ 'status' ] = 'error';
                // возвращаем ответ
                return json_encode( $response );
            endif;
        else:
            // если нет, авторизуем
            $userAuthModel = new UserAuthModel;
            $userAuthModel->userId = $user[ '_id' ];
            $userAuthModel->authKey = $userAuthKey;
            // сохраняем в базу
            if( !$userAuthModel->save() ):
                // пишем лог
                $this->log( 'userSignIn: ошибка сохранения auth_key для юзера с id=' . $user[ '_id' ] );
                // статус - ошибка
                $response[ 'status' ] = 'error';
                // возвращаем ответ
                return json_encode( $response );
            endif;
        endif;

        $response[ 'status' ] = 'success';
        $response[ 'user_auth_key' ] = $userAuthKey;

        return $response;
    }

    public function getSubjectsForCategory( Request $request )
    {
        // проверяем передан ли id и корректно ли передан
        if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )
            OR !$this->isMongoId( $id )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Некорректный id!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // получаем темы для категории с этим _id
        if( !$subjects = SubjectModel::where( 'subjectCategory', '=', $id )->get( [ '_id', 'title' ] )
            OR !$subjects = $subjects->toArray()
            OR !is_array( $subjects )
            OR empty( $subjects )
            OR !$subjects = $this->changeId( $subjects )
            ):
            // пишем лог
            $this->log( 'getSubjectsForCategory: Не получены subjects!' );
            // возвращаем пустой массив
            return json_encode( array() );
        endif;

        // если всё ОК
        return json_encode( $subjects );
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

        return TRUE;
    }

    private function debug( $var )
    {
        echo '<pre>';
        var_dump( $var );
        echo '</pre>';

        return TRUE;
    }

    private function changeId( $object )
    {
        if( !is_array( $object )
            OR empty( $object )
            ):
            return FALSE;
        endif;

        if( isset( $object[ '_id' ] ) ):
            $object[ 'id' ] = $object[ '_id' ];
            unset( $object[ '_id' ] );
            return $object;
        else:
            foreach( $object as $key => $value ):
                if( $result = $this->changeId( $value )
                    AND is_array( $result )
                    AND !empty( $result )
                    ):
                    $object[ $key ] = $result;
                endif;
            endforeach;

            return $object;
        endif;
    }

    private function isMongoId( $mongoId )
    {
        if( !is_string( $mongoId )
            OR mb_strlen( $mongoId ) !== 24
            OR !ctype_xdigit( $mongoId )
            ):
            return FALSE;
        endif;

        return TRUE;
    }

    private function isUserAuth( $userId )
    {
        if( !$this->isMongoId( $userId )
            OR !$userAuth = UserAuthModel::where( 'userId', '=', $userId )->first()
            ):
            return FALSE;
        endif;

        return $userAuth;
    }
}
