<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceName extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'county_id',
    ];

    protected $hidden = [
        'county_id',
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
