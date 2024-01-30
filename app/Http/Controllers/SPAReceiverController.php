<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use App\Models\Signer;
use App\Services\Bitrix24\Facades\Bitrix24;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class SPAReceiverController extends Controller
{
    public function requestSignature(Request $request){
        $data = $request->validate([
            'spa_path'=>['required'],
            'template_id'=>['required'],
            'signer_id'=>['required'],
            'signer_name'=>['required'],
            'signer_division'=>['required'],
            'signer_position'=>['required'],
            'title'=>['required'],
            'subject'=>[],
            'target_field'=>['required']
        ]);
        $data['signer_id'] = intval($data['signer_id']);
        $spa = explode('/',$data['spa_path']);
        if (!isset($spa[3]) or !isset($spa[5])){
            return abort(501);
        }
        $spa_id = $spa[3];
        $spa_item_id = $spa[5];
        $spa = $this->loadSPA($spa_id,$spa_item_id);
        if (count($spa)<1){
            return abort(501);
        }
        Signer::query()->updateOrInsert([
            'id'=>$data['signer_id']
        ],[
            'name'=>$data['signer_name'],
            'division'=>$data['signer_division'],
            'position'=>$data['signer_position'],
        ]);
        /**
         * @var Signer $signer
         */
        $signer = Signer::query()->where('id',$data['signer_id'])->first();
        if (!$signer?->exists){
            return abort(501);
        }
        /**
         * @var Signature $signature
         */
        $signature = $signer->signatures()->create([
            'spa_id'=>$spa_id,
            'item_id'=>$spa_item_id,
            'field_id'=>$data['target_field'],
            'template_id'=>intval($data['template_id']),
            'title'=>$data['title'],
            'subject'=>$data['subject'],
            'data'=>json_encode($spa),
            'signing_token'=>$this->getSigningToken(),
            'signing_passcode'=>$this->getPasscode(8)
        ]);
        if (!$signature?->exists){
            return abort(501);
        }
        return $this->notifySigner($signature);
    }
    private function loadSPA($spa_id, $spa_item_id){
        Bitrix24::selectSpa($spa_id);
        Bitrix24::selectItem($spa_item_id);

        return Bitrix24::getSPA();
    }
    private function notifySigner(Signature $signature){
        $url = route('signing',$signature->signing_token);
        $message = 'Please sign "'.$signature->title.'" by [URL='.$url.']Sign #'.$signature->id.'[/URL]. And using this passcode: [B]'.$signature->signing_passcode.'[/B].';
        Bitrix24::notifyUser($signature->signer_id,$message);
        return abort(200);
    }
    private function getSigningToken(){
        $uuid = Uuid::uuid4()->getHex();
        $uuid = base_convert($uuid,16,36);
        $hashed_time = hash('sha1',microtime(true));
        $hashed_time = base_convert($hashed_time,16,36);
        return $uuid.'.'.$hashed_time;
    }

    private function getPasscode($size = 8): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        try {
            for ($i = 0; $i < $size; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } catch (\Exception $e){
            return substr(Uuid::uuid4(),0,$size);
        }

        return $randomString;
    }
}
