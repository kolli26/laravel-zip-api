<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceName extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name'
    ];

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function zipCodes()
    {
        return $this->hasMany(ZipCode::class);
    }
}
