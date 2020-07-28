<?php

namespace App\Console\Commands;

use App\ItunesParserStatus;
use App\Jobs\Parser\CollectItunesLinkJob;
use App\Services\ItunesParser as ParserService;
use Illuminate\Console\Command;
use App\Movie;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ItunesLinkParserCommand
 *
 * @package App\Console\Commands
 */
class ItunesLinkParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:links';

    /**
     * The console command description.
     *a
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var string
     */
    protected $link = 'https://itunes.apple.com/';

    /**
     * @var int
     */
    protected $elements = 10;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        $movies = Movie::all();
        $count = $movies->count();
        $chunkedMovies = $movies->chunk($this->elements);
        $hash = uniqid();

        $data = [
            'status' => 'processing',
            'parsed' => '0',
            'total' => $count,
        ];

        $itunesParserStatus = ItunesParserStatus::create($data);

        foreach ($chunkedMovies as $movies) {
            $parsedCount = $this->prepare($movies, $hash, $itunesParserStatus);
        }
        $parsedItems = Movie::where('hash', $hash)->count();
        if($parsedItems == $count ) {
            $itunesParserStatus->status = 'finished';
            $itunesParserStatus->update();
        } else {
            $itunesParserStatus->status = 'error';
            $itunesParserStatus->update();
        }

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        if($parsedCount = 0) {
            return printf("%s \n", "All items was successfully parsed. Time spent: $time.");
        }
    }

    /**
     * Update status of itunes parser
     *
     * @param $movies
     * @param $hash
     * @param $itunesParser
     */
    public function prepare($movies, $hash, $itunesParserStatus)
    {

        foreach ($movies as $movie) {
            $this->getItunesLink($movie, $hash);
        }

        $parsedItems = Movie::where('hash', $hash)->count();
        $itunesParserStatus->parsed = $parsedItems;
        $itunesParserStatus->update();

        return $parsedItems;

    }

    /**
     * Parses the apple page and update itunes_link for the movie model
     *
     * @param $movie
     * @param $hash
     */
    public function getItunesLink($movie, $hash)
    {
        $preparedTitle = strtolower(str_replace(' ', '+', $movie->original_title));
        $htmlLink = "$this->link/search?term=$preparedTitle&media=movie&limit=25";
        $response = Http::get($htmlLink);
        $crawler = new Crawler((string) $response->getBody());
        $data = [];

        $responseFilter = '//body//p';
        if ($crawler->filterXPath($responseFilter)->count() > 0) {
            $json = $crawler->filterXPath($responseFilter)->text();
            $data = json_decode($json, true);
        }
        if(!empty($data['results'])) {
            foreach ($data['results'] as $result) {
                if($result['artistName'] == $movie->director) {
                    $url = $result['trackViewUrl'];
                    $movie->itunes_link = $url;
                }
            }
        }

        $movie->hash =$hash;
        $movie->update();
    }
}
