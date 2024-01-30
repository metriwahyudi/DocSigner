<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use App\Services\Bitrix24\Facades\Bitrix24;
use Illuminate\Http\Request;
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
        return Inertia::render('Signing',[
            'title'=>$signature->title,
        ]);
    }
    private function sign(Signature $signature, Request $request){
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
