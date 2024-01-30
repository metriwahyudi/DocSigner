<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use App\Models\Signer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class SignController extends Controller
{

    const APP_ACCESS_CODE = '343dsfsdfsdf4223rdssfs34322';

    public function create(Request $request){
        if (!$request->has('access_code')){
            return abort(404);
        }
        if ($request->get('access_code') != self::APP_ACCESS_CODE){
            return abort(404);
        }
       $data = $request->validate([
           'signer_id'=>['required',Rule::exists(Signer::class,'id')],
           'number'=>['required'],
           'ref_doc_id'=>[],
           'reference'=>[]
       ]);

        if (isset($data['reference'])){
            $data['reference'] = json_encode($data['reference']);
        }
        if ($request->hasFile('file')){
            $file = $request->file('file');
            $data['file_path'] = $file->storeAs('documents/'.$data['signer_id'].'/',Uuid::uuid4().'.'.$file->extension());
            if (!$data['file_path']){
                return abort(501,'Upload failed');
            }
        }

        return (new \App\Services\Signer\Signer())->makeSignature($data,$request->has('link_only'));

    }
    public function verify($sign){
        $signature = Signature::with(['signer'])->where('signature',$sign)->first();
        dd($signature);
    }
}
