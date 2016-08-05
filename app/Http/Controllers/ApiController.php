<?php

namespace App\Http\Controllers;

use Log;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\SubjectCategoryModel;

use App\SubjectModel;

use App\WebinarModel;

use App\UserModel;

class ApiController extends Controller
{
    public function categoriesGetAll()
    {
    	// Получаем список категорий, проверяем корректность
    	if ( !$result = SubjectCategoryModel::all()->toArray() OR !is_array( $result ) OR empty( $result ) ):
    		// Если не получили, пишем лог (пока не работает)
    		Log::error( 'categoriesGetAll: Список не получен!' );
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
    		Log::error( 'webinarsFromCategory: Некорректный alias!' );
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
    		Log::error( 'webinarsFromCategory: Не получена категория с алиасом ' . $alias . '!' );
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
    		Log::error( 'webinarsFromCategory: Не получены темы, входящие в категорию с алиасом ' . $alias . '!' );
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
    		Log::error( 'webinarsFromCategory: Не получены ID тем, входящих в категорию с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// Получаем вебинары и проверяем корректность получения
    	if( !$webinars = WebinarModel::whereIn( 'subject', $subjectIds )->get() 
    		OR !$webinars = $webinars->toArray()
    		OR empty( $webinars )
    		):
    		// пишем лог
    		Log::error( 'webinarsFromCategory: Не получены вебинары категории с алиасом ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// помещаем вебинары в категорию
    	$subjectCategory[ 'webinars' ] = $webinars;

    	// возвращаем
    	return json_encode( $subjectCategory );
    }

    // public function oneWebinarWithCatAndSub( Request $request )
    // {
    // 	try
    // 	{
    // 		// проверяем передан ли alias и корректно ли передан
	   //  	if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
	   //  		OR !is_string( $alias ) 
	   //  		OR empty( $alias ) 
	   //  		):
	   //  		// пишем лог
	   //  		Log::error( 'oneWebinarWithCatAndSub: Некорректный alias!' );
	   //  		// возвращаем пустой массив
	   //  		return json_encode( array() );
	   //  	endif;

	   //  	$alias = '/' . $alias;

	   //  	// эта херня работает (через точку элемент вложенного массива)
	   //  	$webinar = WebinarModel::where( 'seo.alias', '=', $alias )->get()->toArray();

	   //  	// $webinar = $webinar[ 0 ];

	    	

	   //  	$subjectId = $webinar[ 0 ][ 'subject' ];

	   //  	var_dump( $subjectId );

	   //  	// $subject = SubjectModel::where( '_id', '=', $subjectId )->get()/*->toArray()*/;
	   //  	$subject = SubjectModel::find( $subjectId )/*->get()->toArray()*/;

	   //  	echo '<pre>';
	   //  	var_dump( $subject );
	   //  	echo '</pre>';

	    	
    // 	}
    // 	catch( Exception $e )
	   //  {
	   //  	Log::critical( 'oneWebinarWithCatAndSub: Exception: ' . $e->getMessage() );
	   //  }
    // }

    public function getExperts( Request $request )
    {
    	// проверяем передан ли alias и корректно ли передан
    	if( !$alias = strip_tags( stripslashes( trim( $request->input( 'alias' ) ) ) )  
    		OR !is_string( $alias ) 
    		OR empty( $alias ) 
    		):
    		// пишем лог
    		Log::error( 'getExperts: Некорректный alias!' );
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
    		Log::error( 'getExperts: Не получена subjectCategory!' );
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
    		Log::error( 'getExperts: Не получены эксперты для категории ' . $alias . '!' );
    		// возвращаем пустой массив
    		return json_encode( array() );
    	endif;

    	// если всё ок, засовываем в категорию её экспертов
    	$subjectCategory[ 'experts' ] = $experts;

    	// преобразуем в JSON и возвращаем
    	return json_encode( $subjectCategory );
    }
}
