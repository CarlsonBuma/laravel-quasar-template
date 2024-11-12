<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PivotCollaborationSkills extends Model
{
    use HasFactory;

    protected $table = 'public.pivot_collaboration_skills';

    protected $fillable = [
        'collaboration_id',
        'skill_id',
        'description',
    ];

    public function belongs_to_collaboration() {
        return $this->belongsTo(UserEntityCollaborations::class, 'collaboration_id');
    }

    public function belongs_to_skill() {
        return $this->belongsTo(AppSkills::class, 'skill_id');
    }
}
