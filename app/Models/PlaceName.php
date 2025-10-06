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

    public function zipCodes()
    {
        return $this->hasMany(ZipCode::class);
    }
}
