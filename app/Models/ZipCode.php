<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'place_name_id',
        'county_id'
    ];

    public function placeName()
    {
        return $this->belongsTo(PlaceName::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}
