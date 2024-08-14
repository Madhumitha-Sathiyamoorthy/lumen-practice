<?php

namespace App\Console\Commands;

use Dotenv\Result\Success;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Memes;
use Carbon\Carbon;

class AddMemes extends Command
{

    // use CommonTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'memes:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Adding memes into a database.";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $getMemes = Http::get('https://api.imgflip.com/get_memes');
            $response = $getMemes->json();
            if ($response['success']) {
                foreach ($response['data']['memes'] as $meme) {
                    $memeExists = Memes::where('meme_id', '=', $meme['id'])->first();
                    if ($memeExists) {
                        Memes::where('_id', $memeExists['_id'])->increment('count');
                    } else {
                        Memes::insert([
                            'meme_id' => $meme['id'],
                            'name' => $meme['name'],
                            'url' => $meme['url'],
                            'width' => $meme['width'],
                            'height' => $meme['height'],
                            'box_count' => $meme['box_count'],
                            'captions' => $meme['captions'],
                            'count' => 1,
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ]);
                    }
                }
                $this->info('Data inserted successfully');
            } else {
                $this->error('Unable to fetch data from API :(');
            }
        } catch (\Throwable $e) {
            $this->error('Something went wrong ! ' . $e->getMessage());
        }
    }
}