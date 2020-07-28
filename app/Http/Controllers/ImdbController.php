<?php

namespace App\Http\Controllers;

use App\Movie;
use App\Services\ImdbParser;

/**
 * Class ImdbController
 *
 * @package App\Http\Controllers
 */
class ImdbController extends Controller
{
    /**
     * ImdbController constructor.
     *
     * @param \App\Services\ImdbParser $imdbParser
     */
    public function __construct(ImdbParser $imdbParser)
    {
        $this->imdbParser = $imdbParser;
    }

    /**
     * @param $id
     */
    public function test($id) {
        $movies = Movie::all();
        foreach ($movies as $movie) {
           // if(isset($movie->original_title) && isset($movie->director)) {
                $test = $this->imdbParser->getItunesLink($movie);
          //  }
        }
       // $test = $this->imdbParser->getItunesLink(59);

    }

    /**
     *
     */
    public function test1() {
        $this->imdbParser->getMostPopularMovies();
    }

}
