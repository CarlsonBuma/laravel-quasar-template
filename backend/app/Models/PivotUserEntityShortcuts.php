<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


// *********************************
// * Many users (user_id) are connected
// * to many entities (entity_id)
// *********************************
class PivotUserEntityShortcuts extends Model
{
    use HasFactory;

    protected $table = 'public.pivot_user_entity_shortcuts';

    protected $fillable = [
        'user_id',
        'entity_id',
        'notes'
    ];

    public function belongs_to_user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_entity() {
        return $this->belongsTo(UserEntity::class, 'entity_id');
    }
}
