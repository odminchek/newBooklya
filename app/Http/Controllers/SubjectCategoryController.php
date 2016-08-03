<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\SubjectCategoryModel;

class SubjectCategoryController extends Controller
{
    public function apiGetAll()
    {
    	return SubjectCategoryModel::all();
    }
}
