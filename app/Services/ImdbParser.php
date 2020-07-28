<?php
namespace App\Services;

use App\Movie;
use http\Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;

/**
 * Class ImdbParser
 *
 * @package App\Services
 */
class ImdbParser {
    /**
     * @var string
     */
    protected $link = 'https://www.imdb.com';

    /**
     * This method allows you to get movie data by imdb_id
     *
     * @param $id
     * @return array
     */
    public function getMovieData($id) {

        $htmlLink = "$this->link/title/$id";
        $response = Http::get($htmlLink);
        $crawler = new Crawler((string)$response->getBody());
        $scriptFilter = '//script[@type="application/ld+json"]';
        $titleFilter = '//div[@class="title_wrapper"]//h1/text()';
        $originalTitleFilter = '//div[@class="originalTitle"]';
        $releaseDateFilter = '//a[@title="See more release dates"]';
        $ratingFilter = '//span[@itemprop="ratingValue"]';

        $scrapedData = [];
        try {
            $object = json_decode($crawler->filterXPath($scriptFilter)->text(), true) ?? [];
            $title = null;
            $originalTitle = null;
            $rate = null;
            $category = null;
            $director = null;
            $image = null;
            $releaseDate = null;
            if($crawler->filterXPath($originalTitleFilter)->count() > 0) {
                $originalTitle = $crawler->filterXPath($originalTitleFilter)->text();
            }
            if($crawler->filterXPath($titleFilter)->count() > 0) {
                $title = $crawler->filterXPath($titleFilter)->text();
            }
            if($crawler->filterXPath($ratingFilter)->count() > 0) {
                $rate = $crawler->filterXPath($ratingFilter)->text();
            }
            if($crawler->filterXPath($releaseDateFilter)->count() > 0) {
                $releaseDate = trim($crawler->filterXPath($releaseDateFilter)->html());
            }
            if(!empty($object['name'])) {
                $originalTitle = $object['name'];
            } else {
                $OriginalTitle = $title;
            }
            if(!empty($object['genre'])) {
                //Check for the case when the film has only one genre. In this case, the gendre is a string.
                if(is_string($object['genre'])) {
                    $category = $object['genre'];
                } else {
                    $category = implode($object['genre'], ",");
                }
            }
            if(!empty($object['director'])) {
                $director = $object['director']['name'] ?? null;
            }
            if(!empty($object['image'])) {
                $image = $object['image'] ?? null;
            }

            $scrapedData = [
                'imdb_id' => $id,
                'title' => $title,
                'original_title' => $originalTitle,
                'image' => $image,
                'rate' => $rate,
                'release_date' => $releaseDate,
                'category' => $category,
                'director' => $director,
            ];

        } catch (Exception $exception) {
            Log::error('ImdbParser: Error when trying to get data. Check the filter', [
              'imdb_id' => $id,
              'error' => $exception->getMessage(),
            ]);
        }
        $msg = $this->updateOrCreate($scrapedData);

        return $msg;
    }

    /**
     * This method allows you to get the most popular movies
     *
     * @return array
     */
    public function getMostPopularMovies() {

        $htmlLink = "$this->link/chart/moviemeter";
        $response = Http::get($htmlLink);
        $crawler = new Crawler((string)$response->getBody());
        $topRatedFilter = '//tbody[@class="lister-list"]//tr//td[@class="watchlistColumn"]//div[@data-tconst]';
        $movieIds = [];
        try {
            $movieIds[] = $crawler->filterXPath($topRatedFilter)->each(function (Crawler $node, $i) {
                    return $node->attr('data-tconst');
            });
        } catch (\Exception $exception) {
            Log::error('ImdbParser: Error processing the most popular movie data', [
                'error' => $exception->getMessage(),
            ]);
        }

        if(empty($movies)) {
            Log::warning('ImdbParser: Error when trying to get most popular movies. Check the filter');
        }

        //Trying to get movie data by imdb_id
        foreach($movieIds[0] as $key => $id) {
            $this->getMovieData($id);
        }
    }

    /**
     * This method created new entries and updates old ones if the ratings has changed.
     *
     * @param array $data
     */
    public function updateOrCreate(array $data = array())
    {
        $movie = Movie::firstOrNew(['imdb_id' => $data['imdb_id']], $data);

        if($movie->exists) {
            if(($movie->original_title != $data['original_title']) || ($movie->rate != $data['rate'])) {
                $movie->update($data);
                return  printf("%s \n", "Movie with IMDB ID: $movie->imdb_id was updated.");
            }
            return  printf("%s \n", "IMDB ID: $movie->imdb_id. Nothing to update.");
        } else {
            $movie->create($data);
        }

        return  printf("%s \n", "Movie with IMDB ID: $movie->imdb_id was created.");
    }
}

