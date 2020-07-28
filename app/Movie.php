<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Movie
 *
 * @package App
 */
class Movie extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'imdb_id',
        'title',
        'original_title',
        'image',
        'rate',
        'release_date',
        'category',
        'director',
        'itunes_link',
    ];
}
