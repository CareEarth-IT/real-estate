<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareEarthUser extends Model
{
    protected $table = 'careearth_users';

    protected $fillable = [
        'email',
        'password_hash',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
