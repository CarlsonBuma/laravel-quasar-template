<?php

namespace App\Models;

use App\Models\PivotCollaborationSkills;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppSkills extends Model
{
    use HasFactory;

    protected $table = 'public.app_skills';

    protected $fillable = [
        'is_public',
        'label',
        'description',
    ];

    public function has_collaborations() {
        return $this->hasMany(PivotCollaborationSkills::class, 'skill_id');
    }
}
