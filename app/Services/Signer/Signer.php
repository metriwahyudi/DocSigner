<?php

namespace App\Services\Signer;

use App\Models\Document;

class Signer
{

    public function makeSignature($data, $link_only = false){
        if (!Document::query()->updateOrInsert(['number'=>$data['number']],$data)){
            return abort(501,'Document creation failed.');
        }
        $document = Document::query()->where('number',$data['number'])->first();
        if (!isset($document->id)){
            return abort(501,'Document after creation failed.');
        }
        $document->sign = \hash('sha512',microtime(true).'-'.$document->id.'-'.$document->signer_id);
        if (!$document->save()){
            return abort(501,'Signing failed.');
        }
        $url = route('sign.verify',$document->sign);
        if ($link_only){
            return response()->json([
                'url'=>$url
            ]);
        }
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->generate($url);
    }
}
