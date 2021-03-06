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
use App\MessageModel;
use App\LessonModel;
use Config;
use App\OauthAccessTokenScopesModel;

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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
    		return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // получаем статьи, соотв. этому юзеру, и проверяем
        if( !$articles = ArticleModel::where( 'user', '=', $id )->get()
            OR !$articles = $articles->toArray()
            OR !is_array( $articles )
            OR empty( $articles )
            OR !$articles = $this->changeId( $articles )
            ):
            $this->log( 'getArticlesByUser: Не получены статьи юзера с id ' . $id );
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // получаем отзывы, соотв. этому юзеру, и проверяем
        if( !$feedbacks = FeedbackModel::where( 'userWhom', '=', $id )->get()
            OR !$feedbacks = $feedbacks->toArray()
            OR !is_array( $feedbacks )
            OR empty( $feedbacks )
            OR !$feedbacks = $this->changeId( $feedbacks )
            ):
            $this->log( 'getFeedbacksForUser: Не получены отзывы юзера с id ' . $id );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        return json_encode( $feedbacks );
    }

    public function userSignIn( Request $request )
    {
        // смотрим что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !isset( $body[ 'username' ] )
            OR !is_string( $body[ 'username' ] )
            OR empty( $body[ 'username' ] )
            OR !isset( $body[ 'password' ] )
            OR !is_string( $body[ 'password' ] )
            OR empty( $body[ 'password' ] )
            ):
            $this->log( 'userSignIn: Учётные данные некорректны!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // пытаемся найти такого юзера
        if( !$userModel = UserModel::where( 'phoneNumber', '=', $body[ 'username' ] )->orWhere( 'primaryEmail', '=', $body[ 'username' ] )->first()
            OR !$user = $userModel->toArray()
            OR !is_array( $user )
            OR empty( $user )
            OR !isset( $user[ '_id' ] )
            ):
            $this->log( 'userSignIn: Не найден пользователь с username = ' . $body[ 'username' ] . '!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверяем пароль
        if( !isset( $user[ 'password' ] ) 
            OR !is_string( $user[ 'password' ] )
            OR !mb_strlen( $user[ 'password' ] )
            OR $user[ 'password' ] !== md5( $body[ 'password' ] )
            ):
            $this->log( 'userSignIn: Неверный пароль для пользователя с username = ' . $body[ 'username' ] . '!' );
            return json_encode( [ 'status' => 'error' ] );
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
                // возвращаем ответ
                return json_encode( [ 'status' => 'error' ] );
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
                // возвращаем ответ
                return json_encode( [ 'status' => 'error' ] );
            endif;
        endif;

        return json_encode( [ 'status' => 'success', 'user_auth_key' => $userAuthKey ] );
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
            return json_encode( [ 'status' => 'error' ] );
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
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // если всё ОК
        return json_encode( $subjects );
    }

    public function createFeedback( Request $request )
    {
        // проверяем что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !is_array( $body )
            OR !isset( $body[ 'for_user_id' ] )
            OR !$this->isMongoId( $body[ 'for_user_id' ] )
            OR !isset( $body[ 'text' ] )
            OR !is_string( $body[ 'text' ] )
            OR empty( $body[ 'text' ] )
            ):
            $this->log( 'createFeedback: некорректные параметры запроса!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверяем, что существует юзер, о котором нужно оставить отзыв
        if( !UserModel::find( $body[ 'for_user_id' ] ) ):
            $this->log( 'createFeedback: пользователь с _id=' . $body[ 'for_user_id' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // создаём экземпляр класса и заполняем поля
        $feedbackModel = new FeedbackModel;
        $feedbackModel->user = $body[ 'user_id' ];
        $feedbackModel->userWhom = $body[ 'for_user_id' ];
        $feedbackModel->text = $body[ 'text' ];

        // сохраням в базу
        if( !$result = $feedbackModel->save() 
            OR !$insertedId = $feedbackModel->_id
            OR !$this->isMongoId( $insertedId )
            ):
            $this->log( 'createFeedback: не удалось сохранить модель! user=[' . $body[ 'user_id' ] . '] userWhom=[' . $body[ 'for_user_id' ] . '] text=[' . $body[ 'text' ] . ']' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // возвращаем ID добавленного отзыва
        return json_encode( [ 'status' => 'success', 'inserted_id' => $insertedId ] );
    }

    public function createMessage( Request $request )
    {
        // проверяем что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !is_array( $body )

            OR !isset( $body[ 'interlocutor' ] )
            OR !$this->isMongoId( $body[ 'interlocutor' ] )

            OR !isset( $body[ 'text' ] )
            OR !is_string( $body[ 'text' ] )
            OR empty( $body[ 'text' ] )
            ):
            $this->log( 'createMessage: некорректные параметры запроса!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверяем, что существует юзер, которому адресовано сообщение
        if( !UserModel::find( $body[ 'interlocutor' ] ) ):
            $this->log( 'createMessage: пользователь с _id=' . $body[ 'interlocutor' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // создаём экземпляр класса, заполняем поля и сохраняем в базу (первое сообщение, для получателя)
        $messageModel = new MessageModel;
        $messageModel->user = $body[ 'user_id' ];
        $messageModel->interlocutor = $body[ 'interlocutor' ];
        $messageModel->text = $body[ 'text' ];
        $messageModel->incoming = TRUE;
        $messageModel->outgoing = FALSE;
        $messageModel->isRead = FALSE;

        // сохраняем и проверяем
        if( !$messageModel->save()
            OR !$firstInsertedId = $messageModel->_id
            OR !$this->isMongoId( $firstInsertedId )
            ):
            $this->log( 'createMessage: Сообщение для получателя не сохранено!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;
        
        // создаём экземпляр класса, заполняем поля и сохраняем в базу (второе сообщение, для отправителя)
        $messageModel = new MessageModel;
        $messageModel->user = $body[ 'user_id' ];
        $messageModel->interlocutor = $body[ 'interlocutor' ];
        $messageModel->text = $body[ 'text' ];
        $messageModel->incoming = FALSE;
        $messageModel->outgoing = TRUE;
        $messageModel->isRead = FALSE;

        // сохраняем в базу и получаем ID добавленной записи
        if( !$messageModel->save()
            OR !$insertedId = $messageModel->_id
            OR !$this->isMongoId( $insertedId )
            ):
            $this->log( 'createMessage: Сообщение для отправителя не сохранено!' );
            // сносим сообщение, которое уже было добавлено
            if( !$messageModel = MessageModel::find( $firstInsertedId )
                OR !$messageModel->delete()
                ):
                // если не удалось снести, пишем лог
                $this->log( 'createMessage: Сообщение для получателя не удалено!' );
            endif;
            // ошибку в статус и возвращаем
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // возвращаем
        return json_encode( [ 'status' => 'success', 'new_message_id' => $messageModel->_id ] );
    }

    public function createLesson( Request $request )
    {
        // проверяем что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !is_array( $body )

            OR !isset( $body[ 'start_date' ] )
            OR !is_numeric( $body[ 'start_date' ] )

            OR !isset( $body[ 'stop_date' ] )
            OR !is_numeric( $body[ 'stop_date' ] )

            OR !isset( $body[ 'price' ] )
            OR !is_numeric( $body[ 'price' ] )

            OR !isset( $body[ 'subject' ] )
            OR !$this->isMongoId( $body[ 'subject' ] )

            OR !isset( $body[ 'theme' ] )
            OR !is_string( $body[ 'theme' ] )
            OR empty( $body[ 'theme' ] )
            ):
            $this->log( 'createLesson: некорректные параметры запроса!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверяем, что существует тема, к которой будет относиться добавляемый урок
        if( !SubjectModel::find( $body[ 'subject' ] ) ):
            $this->log( 'createLesson: subject с _id=' . $body[ 'subject' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // создаём экземпляр класса и заполняем поля
        $lessonModel = new LessonModel;
        $lessonModel->learner = $body[ 'user_id' ];
        $lessonModel->startDate = $body[ 'start_date' ];
        $lessonModel->stopDate = $body[ 'stop_date' ];
        $lessonModel->price = $body[ 'price' ];
        $lessonModel->subject = $body[ 'subject' ];
        $lessonModel->theme = $body[ 'theme' ];

        // сохраняем в базу и проверяем
        if( !$lessonModel->save()
            OR !$insertedId = $lessonModel->_id
            ):
            $this->log( 'createLesson: урок не добавлен!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // если всё ОК, формируем ответ
        $response[ 'status' ] = 'success';
        $response[ 'lesson_id' ] = $insertedId;

        // возвращаем
        return json_encode( [ 'status' => 'success', 'lesson_id' => $insertedId ] );
    }

    public function createArticle( Request $request )
    {
        // проверяем что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !is_array( $body )

            OR !isset( $body[ 'title' ] )
            OR !is_string( $body[ 'title' ] )
            OR empty( $body[ 'title' ] )

            OR !isset( $body[ 'subject' ] )
            OR !$this->isMongoId( $body[ 'subject' ] )

            OR !isset( $body[ 'subjectCategory' ] )
            OR !$this->isMongoId( $body[ 'subjectCategory' ] )

            OR !isset( $body[ 'image' ] )
            OR !$this->isMongoId( $body[ 'image' ] )

            OR !isset( $body[ 'text' ] )
            OR !is_string( $body[ 'text' ] )
            OR empty( $body[ 'text' ] )

            OR !isset( $body[ 'alias' ] )
            OR !is_string( $body[ 'alias' ] )
            OR empty( $body[ 'alias' ] )
            ):
            $this->log( 'createArticle: некорректные параметры запроса!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

         // проверки связей
        if( !SubjectModel::find( $body[ 'subject' ] ) ):
            $this->log( 'createArticle: subject с _id=' . $body[ 'subject' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        if( !SubjectCategoryModel::find( $body[ 'subjectCategory' ] ) ):
            $this->log( 'createArticle: subjectCategory с _id=' . $body[ 'subjectCategory' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверка существования subjectCategory
        if( !SubjectCategoryModel::find( $body[ 'subjectCategory' ] ) ):
            $this->log( 'createArticle: subjectCategory с _id=' . $body[ 'subjectCategory' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // проверка существования subject
        if( !$subject = SubjectModel::find( $body[ 'subject' ] ) ):
            $this->log( 'createArticle: subject с _id=' . $body[ 'subject' ] . ' не существует!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // // проверяем соответствие subject и subjectCategory
        if( !isset( $subject )
            OR !is_object( $subject )
            OR !isset( $subject->subjectCategory )
            OR $subject->subjectCategory != $body[ 'subjectCategory' ]
            ):
            $this->log( 'createArticle: subject с _id=' . $body[ 'subject' ] . ' не входит в subjectCategory с _id=' . $body[ 'subjectCategory' ] . '!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // создаём экземпляр класса
        $article = new ArticleModel;

        // заполняем поля
        $article->user               = $body[ 'user_id' ];
        $article->title              = $body[ 'title' ];
        $article->subject            = $body[ 'subject' ];
        $article->subjectCategory    = $body[ 'subjectCategory' ];
        $article->text               = $body[ 'text' ];
        $article->image              = $body[ 'image' ];
        $seo[ 'alias' ]              = $body[ 'alias' ];
        $article->seo                = $seo;
        $article->date               = time();
        $article->views              = 0;

        // сохраняем и проверяем
        if( !$article->save()
            OR !$insertedId = $article->_id
            ):
            $this->log( 'createArticle: не удалось сохранить информацию в базу!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // возвращаем
        return json_encode( [ 'status' => 'success', 'id' => $insertedId ] );
    }

    public function imageUpload( Request $request )
    {
        // проверяем что нам пришло
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            OR !is_array( $body )

            OR !isset( $body[ 'for_type' ] )
            OR !is_string( $body[ 'for_type' ] )
            OR !mb_strlen( $body[ 'for_type' ] )

            OR !isset( $body[ 'filename' ] )
            OR !is_string( $body[ 'filename' ] )
            OR !mb_strlen( $body[ 'filename' ] )

            OR !isset( $body[ 'for_id' ] )
            OR !$this->isMongoId( $body[ 'for_id' ] )

            OR !isset( $body[ 'image' ] )
            OR !is_string( $body[ 'image' ] )
            OR empty( $body[ 'image' ] )
            OR !$image = base64_decode( $body[ 'image' ], TRUE )
            ):
            $this->log( 'imageUpload: входные данные некорректны!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // формируем имя папки для загрузки
        $upload_dir = Config::get('app.image_upload_dir')
            . DIRECTORY_SEPARATOR . $body[ 'user_id' ] 
            . DIRECTORY_SEPARATOR . $body[ 'for_type' ]
            . DIRECTORY_SEPARATOR . $body[ 'for_id' ];

        // проверяем существование папки, если нету - создаём
        if( !file_exists( $upload_dir ) ):
            // создаём папку для сохранения и проверяем
            if( !mkdir( $upload_dir, 0777, TRUE ) ):
                $this->log( 'imageUpload: не удалось создать дирректорию ' . $upload_dir . '!' );
                return json_encode( [ 'status' => 'error' ] );
            endif;
        endif;

        // формируем имя файла для сохранения
        $tmp = pathinfo( $body[ 'filename' ] );
        $file_name = $tmp[ 'filename' ] . '_' . time() . '.' . $tmp[ 'extension' ];

        // формируем имя дирректории для сохранения
        $upload_file_name = $_SERVER[ 'DOCUMENT_ROOT' ] 
            . DIRECTORY_SEPARATOR . $upload_dir 
            . DIRECTORY_SEPARATOR . $file_name;

        // сохраняем изображение в файл
        if( !file_put_contents( $upload_file_name, $image ) ):
            $this->log( 'imageUpload: не удалось сохранить файл ' . $upload_file_name . '!' );
            return json_encode( [ 'status' => 'error' ] );
        endif;

        // формируем ответ
        $response[ 'status' ] = 'success';
        $response[ 'user_id' ] = $body[ 'user_id' ];
        $response[ 'for_type' ] = $body[ 'for_type' ];
        $response[ 'for_id' ] = $body[ 'for_id' ];
        $response[ 'absolute_url' ] = Config::get( 'app.image_domain' ) . DIRECTORY_SEPARATOR . $upload_dir . DIRECTORY_SEPARATOR . $file_name;
        $response[ 'relative_url' ] = $upload_dir . DIRECTORY_SEPARATOR . $file_name;
        $response[ 'filename' ] = $file_name;

        return json_encode( $response );
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
