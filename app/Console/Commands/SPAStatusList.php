<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SPAStatusList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spa:status-list {spaId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $spa_id = $this->argument('spaId');

        $client = Http::setClient(new Client([
            'base_uri'=>env('B24_INBOUND_API'),
            'verify'   => false,
        ]));

        $response = $client->post('crm.status.list.json',[
            'entityTypeId'=>$spa_id
        ]);

        if (!$response->ok()){
            $this->alert($response);
            return null;
        }
        $result = $response->json('result',false);
        print_r($result);
    }
}
