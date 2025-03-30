<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $table = 'downloads';

    // Fields that can be mass-assigned
    protected $fillable = [
        'user_id',
        'paper_id',
    ];

    /**
     * Define the relationship with the User model.
     * Each download belongs to a specific user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the Paper model.
     * Each download is for a specific paper.
     */
    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }
}
