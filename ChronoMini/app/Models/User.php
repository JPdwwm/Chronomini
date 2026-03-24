<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'role_id',
        'zip_code',
        'city'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            // Générer un code alphanumérique unique de 5 caractères
            do {
                $code = strtoupper(Str::random(5));
            } while (self::where('link_code', $code)->exists());
            
            $user->link_code = $code;
        });
    }
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role_id == 1;
    }

    public function isParent()
    {
        return $this->role_id == 2;
    }

    public function isAsmat()
    {
        return $this->role_id == 3;
    }

    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_user');
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }
    
    public function sentConnectionRequests()
    {
        return $this->hasMany(ConnectionRequest::class, 'sender_id');
    }

    public function receivedConnectionRequests()
    {
        return $this->hasMany(ConnectionRequest::class, 'receiver_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
