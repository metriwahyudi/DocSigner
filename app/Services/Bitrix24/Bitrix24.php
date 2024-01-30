<?php

namespace App\Services\Bitrix24;

use App\Services\Bitrix24\Contracts\Bitrix24Interface;
use App\Services\Signer\Signer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class Bitrix24 implements Bitrix24Interface
{

    private $spaID;
    private $itemID;

    public function selectSpa(int $spaID): Bitrix24
    {
        $this->spaID = $spaID;
        return $this;
    }

    public function selectItem(int $itemID): Bitrix24
    {
        $this->itemID = $itemID;
        return $this;
    }

    public function updateSpaImageFile(string $fieldId, string $filename, string $base64_file)
    {
        $method = 'crm.item.update.json';

        $client = Http::setClient(new Client([
            'base_uri'=>env('B24_INBOUND_API'),
            'verify'   => false,
        ]));
        $client->post($this->getEndpoint().$method,[
            'entityTypeId'=>$this->spaID,
            'id'=>$this->itemID,
            'fields'=>[
                $fieldId=>[$filename,$base64_file]
            ]
        ]);
    }

    private function getClient(){
        return Http::setClient(new Client([
            'base_uri'=>$this->getEndpoint(),
            'verify'   => false,
        ]));
    }

    private function getEndpoint(){
        return env('B24_INBOUND_API');
    }

    public function makeSignature($data){
        return (new Signer())->makeSignature($data);
    }

    public function getSPA(): array
    {

        $client = $this->getClient();
        $response = $client->get('crm.item.get.json',[
            'entityTypeId'=>$this->spaID,
            'id'=>$this->itemID
        ]);
        if ($response->ok()){
            return $response->json('result.item',[]);
        }
        return [];
    }

    public function notifyUser(int $user_id, string $message)
    {
        $client = $this->getClient();
        $client->get('im.notify.personal.add',[
            'USER_ID'=>$user_id,
            'MESSAGE'=>$message
        ]);
    }
}
