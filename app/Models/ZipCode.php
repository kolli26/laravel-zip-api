<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    use HasFactory;

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
