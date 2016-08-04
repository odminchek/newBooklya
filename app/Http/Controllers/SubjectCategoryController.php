<?php

namespace App\Http\Controllers;

use Log;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\SubjectCategoryModel;

class SubjectCategoryController extends Controller
{
    public function apiGetAll()
    {
    	// Получаем список категорий, проверяем корректность
    	if ( !$result = SubjectCategoryModel::all()->toArray() OR !is_array( $result ) OR empty( $result ) ):
    		// Если не получили, пишем лог
    		Log::error( 'apiGetAll: Список не получен!' );
    		// Возвращаем FALSE
    		return FALSE;
    	endif;
    	
    	// Если всё хорошо, кодируем в JSON и отдаём
    	return json_encode( $result );
    }
}
