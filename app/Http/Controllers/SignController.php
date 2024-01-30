<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use App\Models\Signer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
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
        $signature = Signature::with(['signer','document'])->whereNotNull('signed_at')->where('signature',$sign)->first();

        if (!isset($signature->id)){
            return $this->sendError('Signature not found.');
        }
        $document = $signature->document;
        if (!isset($document->id)){
            return $this->sendError('Signature not found.');
        }

        if(!Storage::exists($document->file_path)){
            return $this->sendError('Signature has no document.');
        }

        $size = Storage::fileSize($document->file_path);
        $path = Storage::path($document->file_path);

        $hash = hash_file('sha1',$path);
        $filename = basename($path);


        return Inertia::render('Signature',[
            'filename'=>$filename,
            'signer'=>$signature->signer,
            'time'=>$signature->signed_at,
            'document_link'=>route('signing.download',$document->id),
            'hash'=>$hash,
            'sid'=>$signature->id,
            'size'=>$size
        ]);
    }
    private function sendError($message){

        return Inertia::render('Error',[
            'message'=>$message
        ]);
    }
}
