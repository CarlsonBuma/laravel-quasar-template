<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AccessTransactions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessUsers extends Model
{
    use HasFactory;

    protected $table = 'public.access_users';

    protected $fillable = [
        'user_id',
        'transaction_id',
        'entity_id',
        'is_active',
        'access_token',
        'quantity',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date:Y-m-d',
        'created_at' => 'date:Y-m-d',
    ];

    public function belongs_to_user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_transaction() {
        return $this->belongsTo(AccessTransactions::class, 'transaction_id');
    }

    public function belongs_to_entity() {
        return $this->belongsTo(Entity::class, 'entity_id');
    }
}
