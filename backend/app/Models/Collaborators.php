<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collaborators extends Model
{
    use HasFactory;

    protected $table = 'public.collaborators';

    protected $fillable = [
        'user_id',
        'collaboration_id',
        'entity_id',
        'date_requested',
        'date_released',
        'date_issued',
        'date_confirmed',
        'period_start',
        'period_duration',
        'is_public',
        'token',
        'archived',
    ];

    protected $casts = [
        'date_released' => 'date:Y-m-d',
        'date_requested' => 'date:Y-m-d',
        'date_issued' => 'date:Y-m-d',
        'date_confirmed' => 'date:Y-m-d',
        'period_start' => 'date:Y-m-d',
    ];

    public function belongs_to_user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_entity() {
        return $this->belongsTo(UserEntity::class, 'entity_id');
    }

    public function belongs_to_collaboration() {
        return $this->belongsTo(UserEntityCollaborations::class, 'collaboration_id');
    }
}
