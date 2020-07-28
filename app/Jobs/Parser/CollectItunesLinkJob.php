<?php

namespace App\Jobs\Parser;

use App\Movie;
use App\Services\ItunesParser as ParserService;
use App\ItunesParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CollectItunesLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $movies;
    public $hash;
    public $link = 'https://itunes.apple.com/';
    public $timeout = 120;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($movies, $hash, ParserService $parserService)
    {
        $this->movies = $movies;
        $this->hash = $hash;

        $this->parserService = $parserService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count = $this->movies->count();

        $data = [
            'status' => 'processing',
            'parsed' => '0',
            'total' => $count,
        ];

        $itunesParser = ItunesParser::create($data);

        foreach ($this->movies as $movie) {
           $this->getItunesLink($movie, $this->hash);
        }

        $parsedItems = Movie::where('hash', $this->hash)->count();
        $itunesParser->parsed = $parsedItems;
        $itunesParser->update();
    }

    /**
     * @param $movie
     */
    public function getItunesLink($movie, $hash)
    {
        $preparedTitle = strtolower(str_replace(' ', '+', $movie->original_title));
        $htmlLink = "$this->link/search?term=$preparedTitle&media=movie&limit=25";
        $response = Http::get($htmlLink);
        $crawler = new Crawler((string) $response->getBody());
        $data = [];

        $scriptFilter = '//body//p';
        if ($crawler->filterXPath($scriptFilter)->count() > 0) {
            $json = $crawler->filterXPath($scriptFilter)->text();
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
