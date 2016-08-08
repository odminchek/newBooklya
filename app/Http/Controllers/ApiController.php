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

class ApiController extends Controller
{
    private $myLogging = TRUE;

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

  //   public function oneWebinarWithCatAndSub( Request $request )
  //   {
		// // проверяем передан ли alias и корректно ли передан
  //   	if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
  //   		OR !is_string( $alias ) 
  //   		OR empty( $alias ) 
  //   		):
  //   		// пишем лог
  //   		$this->log( 'oneWebinarWithCatAndSub: Некорректный alias!' );
  //   		// возвращаем пустой массив
  //   		return json_encode( array() );
  //   	endif;

  //   	$alias = '/' . $alias;

  //   	// эта херня работает (через точку элемент вложенного массива)
  //   	$webinar = WebinarModel::where( 'seo.alias', '=', $alias )->get()->toArray();

    	

  //   	$subjectId = $webinar[ 0 ][ 'subject' ];

  //   	// $subject = SubjectModel::where( '_id', '=',  $subjectId )->first();
  //       // $subject = SubjectModel::find( $subjectId )/*->get()->toArray()*/;
  //       $subject = SubjectModel::where( '_id', '=', '551d4530bfef316a3c8b7949' )->get();
  //   	// $subject = SubjectModel::find( '551d4530bfef316a3c8b7949' )/*->get()->toArray()*/;
  //       echo '<pre>';
  //       var_dump( $subject );
  //       echo '</pre>';




  //   }

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

    // public function getOneExpert( Request $request )
    // {
    // 	проверяем передан ли id и корректно ли передан
    // 	if( !$id = strip_tags( stripslashes( trim( $request->input( 'id' ) ) ) )  
    // 		OR !is_string( $id ) 
    // 		OR empty( $id )
    // 		OR mb_strlen( $id ) != 24
    //      OR !ctype_xdigit( $id )
    // 		):
    // 		// пишем лог
    // 		Log::error( 'getOneExpert: Некорректный id эксперта!' );
    // 		// возвращаем пустой массив
    // 		return json_encode( array() );
    // 	endif;

    // 	echo '<pre>';
    // 	var_dump( UserModel::where( '_id', '55d08122bfef31f6098bb2f1' )->get()->toArray() );
    // 	// var_dump( UserModel::find( '55d08122bfef31f6098bb2f1' ) );
    // 	// var_dump( UserModel::all() );
    // 	echo '</pre>';
    // }

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

    private function log( $msg )
    {
        
        if( $this->myLogging ):
            date_default_timezone_set( 'Europe/Samara' );
            $timestamp = date( "Y.m.d H:i:s" ); 
            file_put_contents( '/tmp/lara.log', $timestamp . ' ' . $msg . "\n", FILE_APPEND );
        else:
            Log::error( $msg );
        endif;
    }
}
