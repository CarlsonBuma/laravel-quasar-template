<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppDepartements extends Model
{
    use HasFactory;

    protected $table = 'public.app_departements';

    protected $fillable = [
        'is_public',
        'label',
        'description',
        'responsibilities',
    ];

    protected $casts = [
        'responsibilities' => 'array',
    ];

    public function has_collaborations() {
        return $this->hasMany(UserEntityCollaborations::class, 'departement_id');
    }
}
