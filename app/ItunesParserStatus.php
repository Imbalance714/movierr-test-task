<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItunesParserStatus extends Model
{
    protected $table = "itunes_parser_status";
    protected $fillable = [
        'status',
        'parsed',
        'total',
    ];
}
