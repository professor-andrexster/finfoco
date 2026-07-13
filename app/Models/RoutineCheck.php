<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineCheck extends Model
{
    protected $fillable = ['routine_id', 'data'];
    protected $casts    = ['data' => 'date'];

    public function routine() { return $this->belongsTo(Routine::class); }
}
