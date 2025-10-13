<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'code',
        'place_name_id',
        'county_id'
    ];

    protected $hidden = [
        'place_name_id',
    ];

    public function placeName()
    {
        return $this->belongsTo(PlaceName::class);
    }
}
