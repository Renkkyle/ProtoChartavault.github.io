<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'papers';
    protected $guarded = ['datetime'];


    // Specify the attributes that are mass assignable
    protected $fillable = [
        'title', 'adviser', 'author', 'emails', 'abstract','college', 'course', 'sdgs', 'file_path', 'uploaded_by','document_type','date_published','keywords','datetime'
    ];
    
    protected $casts = [
        'sdgs' => 'array', // This will automatically cast the JSON string to an array
    ];

    // Disable timestamps if your table doesn't have 'created_at' and 'updated_at' columns
    public $timestamps = false;
    // test test test 
    public function adviserPapers()
{
    return $this->hasMany(Paper::class, 'adviser', 'adviser');
}

protected static function booted()
{
    static::updating(function ($paper) {
        // Prevent changing 'datetime' unless it’s currently null (initial set) or during approval/rejection
        if ($paper->isDirty('datetime') && $paper->getOriginal('datetime') !== null) {
            // Check if the status is being updated to 'Approved' or 'Rejected'
            if ($paper->status != $paper->getOriginal('status') && in_array($paper->status, ['Approved', 'Rejected'])) {
                // Allow datetime update when paper is approved or rejected
                $paper->datetime = now(); // Set to current time
            } else {
                // Revert 'datetime' to its original value
                $paper->datetime = $paper->getOriginal('datetime');
            }
        }
    });
}
public function user()
{
    return $this->belongsTo(User::class, 'uploaded_by'); // Assuming 'uploaded_by' stores the user ID
}
}
