<?php

namespace App\Services\Bitrix24;

use App\Models\Document;
use App\Services\Bitrix24\Contracts\Bitrix24Interface;
use App\Services\Signer\Signer;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

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

    public function getForm(int $id): array
    {
        $client = $this->getClient();
        $response = $client->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('crm.form.get', [
            'id' => $id,
        ]);
        return $response->json('result');
    }

    public function getFormList(): array
    {
        $client = $this->getClient();
        $response = $client->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('crm.form.list');
        return $response->json('result');
    }

    public function loadDocument(int $template_id): Document|null
    {
        $client = $this->getClient();

        $response = $client->get('crm.documentgenerator.document.add',[
            'templateId'=> $template_id,
            'entityTypeId'=> $this->spaID,
            'entityId'=> $this->itemID,
        ]);
        $data = $response->json('result.document');
        $download_url = Arr::get($data,'downloadUrlMachine',false);
        $document_id = Arr::get($data,'id',false);
        if (!$download_url){
            return null;
        }
        $file_path = $this->downloadAndSave($download_url);
        if (!$file_path){
            return null;
        }
        /**
         * @var Document $document
         */
        $document = Document::query()->create([
            'document_id'=>$document_id,
            'file_path'=>$file_path,
            'data'=>json_encode($data),
        ]);
        if (!$document){
            return null;
        }
        return $document;
    }
    private function downloadAndSave($fileUrl, $tmp = true)
    {
        $response = Http::setClient(new Client([
            'base_uri'=>$fileUrl,
            'verify' => false,
        ]))->get('');

        $filename = $this->getFilenameFromHeader($response->header('Content-Disposition'));

        if ($response->successful()) {
            $path = ($tmp ? 'tmp_document/' : 'document/').Uuid::uuid4().'/'.$filename;
            if(!Storage::put($path, $response->body())){
                return false;
            }
            return $path;
        } else {
            return false;
        }
    }
    private function getFilenameFromHeader($contentDispositionHeader)
    {
        $re = '/filename\="(.+)"/m';
        preg_match_all($re, $contentDispositionHeader, $matches, PREG_SET_ORDER, 0);

        return Arr::get($matches,'0.1','noname.tmp');
    }

    public function updateDocument(Document $document): Document|null
    {
        $client = $this->getClient();

        $response = $client->get('crm.documentgenerator.document.update',[
            'id'=> $document->document_id
        ]);

        $doc_url = $response->json('result.document.downloadUrlMachine');

        if (!$doc_url) return null;

        $doc_path = $this->downloadAndSave($doc_url,false);

        if (!$doc_path){
            return null;
        }

        $document->file_path = $doc_path;

        if (!$document->save()){
            return null;
        }

        return $document;
    }
}
