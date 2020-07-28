<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImdbParser;
use Illuminate\Support\Carbon;

class ImdbParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:imdb {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse movies from IMDB by id';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImdbParser $imdbParser)
    {
        parent::__construct();
        $this->imdbParser = $imdbParser;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        $id = $this->argument('id');
        $movie = $this->imdbParser->getMovieData($id);

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        echo $time;
    }
}
