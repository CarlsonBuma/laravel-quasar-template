<?php

namespace App\Models;

use App\Models\UserEntity;
use App\Models\AppAwards;
use App\Models\AppDepartements;
use Illuminate\Database\Eloquent\Model;
use App\Models\PivotCollaborationSkills;
use App\Models\Collaborators;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEntityCollaborations extends Model
{
    use HasFactory;

    protected $table = 'public.user_entity_collaborations';

    protected $fillable = [
        'entity_id',
        'award_id',
        'departement_id',
        'title',
        'meta',
        'duration',
        'about',
        'tasks',
        'duration',
        'details',
        'is_public',
        'access_limit',
        'tags',
        'token',
        'archived'
    ];

    protected $casts = [
        'archived' => 'date:Y-m-d',
        'tasks' => 'array',
        'tags' => 'array',
    ];

    public function belongs_to_entity() {
        return $this->belongsTo(UserEntity::class, 'entity_id');
    }

    public function belongs_to_departement() {
        return $this->belongsTo(AppDepartements::class, 'departement_id');
    }

    public function belongs_to_award() {
        return $this->belongsTo(AppAwards::class, 'award_id');
    }

    public function has_skills_pivot() {
        return $this->hasMany(PivotCollaborationSkills::class, 'collaboration_id');
    }

    public function has_collaborators() {
        return $this->hasMany(Collaborators::class, 'collaboration_id');
    }
}
