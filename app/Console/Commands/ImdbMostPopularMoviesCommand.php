<?php

namespace App\Console\Commands;

use App\Services\ImdbParser;
use Illuminate\Console\Command;

/**
 * Class ImdbMostPopularMoviesCommand
 *
 * @package App\Console\Commands
 */
class ImdbMostPopularMoviesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:popular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse most popular movies from IMDB';

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

        $this->imdbParser->getMostPopularMovies();

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        echo $time;
    }
}
