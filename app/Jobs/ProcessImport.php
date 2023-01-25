<?php

namespace App\Jobs;

use App\Http\Controllers\MainController;
use App\Models\Card;
use App\Models\Client;
use App\Models\Parsed;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;
    public $backoff = [2, 10, 20];

    private $parsedId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parsedId)
    {
        $this->parsedId = $parsedId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $toSave = null;
        $parsedModel = Parsed::find($this->parsedId);
        if($parsedModel) {

            // It means that is the first parsing. Adding new field completed with default 0 and store it
            if($parsedModel->status == 0) {
                $data = \json_decode( file_get_contents(\public_path($parsedModel->originalPath)), true );
                $toSave = collect($data)->map(function($item) {
                    $item['completed'] = 0;
                    return $item;
                })->toArray();

                $json = \json_encode($toSave, \JSON_PRETTY_PRINT);
                \file_put_contents(\public_path('uploads/toParse.json'), \stripslashes($json));

                $parsedModel->status = 1;
                $parsedModel->toParsePath = 'uploads/toParse.json';
                $parsedModel->save();
            }

            // It means that the first parsed was interrupted
            if($parsedModel->status == 1) {
                $data = \json_decode( file_get_contents(\public_path($parsedModel->toParsePath)), true );
                $toSave = $data;
            }

        }
        

        $dataFinal = $toSave;
        if($dataFinal && count($dataFinal) > 0)
        {
            foreach ($dataFinal as $key => $value) {
                // Value contains "one row" of JSON file
                if( $value['completed'] == 0 ) {
                    if( MainController::checkAge($value['date_of_birth']) ) {
                        
                        $card = new Card;
                        $card->type = $value['credit_card']['type'];
                        $card->number = $value['credit_card']['number'];
                        $card->name = $value['credit_card']['name'];
                        $card->expirationDate = $value['credit_card']['expirationDate'];
                        $card->save();
                        
                        $client = new Client;
                        $client->name = $value['name'];
                        $client->address = $value['address'];
                        $client->checked = $value['checked'];
                        $client->description = $value['description'];
                        $client->interest = $value['interest'];
                        $toStore = null;
                        try {
                            $toStore = Carbon::create($value['date_of_birth'])->format('Y-m-d H:i:s');
                        } catch (\Throwable $th) {
                            $toStore = Carbon::createFromFormat('d/m/Y', $value['date_of_birth'])->format('Y-m-d');
                        }

                        $client->dateOfBirth = $toStore;
                        $client->email = $value['email'];
                        $client->account = $value['account'];
                        $client->credit_card = $card->id;
                        $client->save();

                        $dataFinal[$key]['completed'] = 1;
                        $json = \json_encode($dataFinal, \JSON_PRETTY_PRINT);
                        \file_put_contents(\public_path('uploads/toParse.json'), \stripslashes($json));

                        // \sleep(0.1);
                    }
                }
            }
            $parsedModel->status = 2;
            $parsedModel->save();
        }
    }
}
