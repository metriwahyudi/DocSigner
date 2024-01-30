<?php

namespace App\Http\Controllers;

use App\Services\Bitrix24\Facades\Bitrix24;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FormController extends Controller
{
    public function open($id, Request $request){
        $form = Bitrix24::getForm($id);
        if (!$form) return abort(404);


        $embed_script = Arr::get($form,'embedding.scripts.inline.text');
        $form_name = Arr::get($form,'name');

        return view('form',[
            'title'=>$form_name,
            'content'=>$embed_script
        ]);
    }
    public function form_list(){
        $list = Bitrix24::getFormList();
        return view('form-list',[
            'forms'=>$list
        ]);
    }
}
