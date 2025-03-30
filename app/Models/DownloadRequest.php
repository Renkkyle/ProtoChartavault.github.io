<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadRequest extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'paper_id',
        'is_approved',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }
}
