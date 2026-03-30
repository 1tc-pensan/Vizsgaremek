<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ReportImage extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['report_id', 'image_path'];

    protected $appends = ['image_url'];

    const CREATED_AT = 'created_at';

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
