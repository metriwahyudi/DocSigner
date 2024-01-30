<?php

namespace App\Console\Commands;

use App\Services\Bitrix24\Facades\Bitrix24;
use App\Services\Signer\Signer;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SPAWatcher extends Command
{
    private $spa_id;
    private $stage_id;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spa:watcher {spaId} {stageId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->spa_id = $this->argument('spaId');
        $this->stage_id = $this->argument('stageId');
        $hashed_stage = hash('sha1',$this->stage_id);
        $method = 'crm.item.list.json';

        $client = Http::setClient(new Client([
            'base_uri'=>env('B24_INBOUND_API'),
            'verify'   => false,
        ]));

        $response = $client->post($method,[
            'entityTypeId'=>$this->spa_id,
            'filter'=>[
                '@stageId'=>[$this->stage_id]
            ]
        ]);

        if (!$response->ok()){
            $this->alert($response);
            return null;
        }

        $latest_ids = $this->getStageCache($hashed_stage);

        print_r($latest_ids);
        echo "\n";

        $result = $response->json('result.items',false);

        $ids = [];

        foreach ($result as $item){
            $ids[] = $item['id'];
            if (in_array($item['id'],$latest_ids)){
                echo $item['id']."\n";
                $sig = (new Signer())->makeSignature([
                    'signer_id'=>1,
                    'number'=>$item['id']
                ]);
                $sig = \base64_encode($sig);
                Bitrix24::selectSpa($this->spa_id);
                Bitrix24::selectItem($item['id']);
                Bitrix24::updateSpaImageFile(
                    fieldId:'ufCrm12_1704778490',
                    filename:'signature.png',
                    base64_file: $sig);
            }
        }

        $this->setStageCache($hashed_stage,$ids);
    }

    private function getStageCache($key){
        return Cache::get('spa_watcher_'.$key,[]);
    }
    private function setStageCache($key, $ids){

        Cache::forever('spa_watcher_'.$key,$ids);
    }
}
