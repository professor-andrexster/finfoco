<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];

    public function transactions() { return $this->hasMany(Transaction::class); }
    public function bills()        { return $this->hasMany(Bill::class); }
    public function alerts()       { return $this->hasMany(Alert::class); }
    public function reminders()    { return $this->hasMany(Reminder::class); }
    public function categories()   { return $this->hasMany(Category::class); }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'trial_ends_at'     => 'datetime',
            'password'          => 'hashed',
            'lifetime_access'   => 'boolean',
        ];
    }
}
