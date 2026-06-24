<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lokasi extends Model
{
    protected $fillable = ['nama_lokasi', 'latitude', 'longitude', 'deskripsi', 'kategori', 'user_id'];

    /**
     * Get the user that owns this location
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
