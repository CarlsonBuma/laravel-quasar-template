<?php

namespace App\Models;

use App\Models\User;
use App\Models\AppLanguages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PivotUserLanguages extends Model
{
    use HasFactory;

    protected $table = 'public.pivot_user_languages';

    protected $fillable = [
        'user_id',
        'language_id',
        'description',
    ];

    public function belongs_to_user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_language() {
        return $this->belongsTo(AppLanguages::class, 'language_id');
    }
}
