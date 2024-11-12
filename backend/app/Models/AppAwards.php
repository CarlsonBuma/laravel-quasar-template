<?php

namespace App\Models;

use App\Models\UserEntity;
use App\Models\UserEntityCollaborations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppAwards extends Model
{
    use HasFactory;

    protected $table = 'public.app_awards';

    protected $fillable = [
        'entity_id',
        'is_public',
        'access_token',
        'label',
        'description',
        'credits',
        'evaluation',
        'icon',
        'archived'
    ];

    protected $casts = [
        'archived' => 'date:Y-m-d',
    ];

    public function belongs_to_entity() {
        if($this->entity_id === null) return null;
        return $this->belongsTo(UserEntity::class, 'entity_id');
    }

    public function has_collaborations() {
        return $this->hasMany(UserEntityCollaborations::class, 'award_id');
    }
}
