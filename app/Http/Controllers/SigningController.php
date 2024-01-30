<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use App\Services\Bitrix24\Facades\Bitrix24;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Ramsey\Uuid\Uuid;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SigningController extends Controller
{
    public function index($token, Request $request){
        /**
         * @var Signature $signature
         */
        $signature = Signature::query()
            ->whereNull('signed_at')
            ->where('signing_token',$token)
            ->first();
        if (!$signature?->exists){
            return abort(404);
        }
        if ($request->isMethod('post')){
            return $this->sign($signature,$request);
        }
        $document = $signature->document;
        if (!isset($document->id)){
            Bitrix24::selectSpa($signature->spa_id);
            Bitrix24::selectItem($signature->item_id);
            $document = Bitrix24::loadDocument($signature->template_id);
            if (!isset($document)){
                return $this->sendError('Cannot load the document.');
            }
            $signature->document_id = $document->id;
            if (!$signature->save()){
                return $this->sendError('Cannot save the document in local drive.');
            }
        }

        $bitrix_domain =  explode('/rest',env('B24_INBOUND_API'));

        return Inertia::render('Signing',[
            'title'=> $signature->title,
            'subject'=> $signature->subject,
            'signer'=>$signature->signer,
            'crm_link'=> $bitrix_domain[0].'/crm/type/'.$signature->spa_id.'/details/'.$signature->item_id.'/',
            'document_link'=> route('signing.download',$signature->document_id),
        ]);
    }
    public function download(Document $document){

        return Storage::download($document->file_path);
    }
    private function sendError($message){

        return Inertia::render('Error',[
            'message'=>$message
        ]);
    }
    private function loadDocument(Signature $signature){

    }
    private function sign(Signature $signature, Request $request){
        $document = $signature->document;
        if (!isset($document->id)){
            return abort(401,'Document not found.');
        }
        $data = $request->validate([
            'passcode'=>['required']
        ]);
        if ($signature->signing_passcode !== $data['passcode']){
            return abort(401,'Invalid passcode');
        }
        $signature->signature = $this->generateSignature();
        $signature->signed_at = date('Y-m-d H:i:s');
        if (!$signature->save()){
            return abort(501,'Signing failed');
        }
        $this->updateSPASignature($signature);

        if (!Bitrix24::updateDocument($document)){
            $signature->signed_at = null;
            $signature->save();
            return abort(401,'Cannot update the document.');
        }

        return abort(200);
    }
    private function generateSignature(){
        return Uuid::uuid4()->getHex()->toString().microtime(true);
    }
    private function updateSPASignature(Signature $signature){
        $string = route('sign.verify',$signature->signature);
        $qr_image = QrCode::format('png')->generate($string);
        Bitrix24::selectSpa($signature->spa_id);
        Bitrix24::selectItem($signature->item_id);

        Bitrix24::updateSpaImageFile(
            fieldId:$signature->field_id,
            filename:'signature-'.$signature->id.'.png',
            base64_file: base64_encode($qr_image));
    }
}
