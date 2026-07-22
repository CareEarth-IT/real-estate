<?php

namespace App\Models;

use App\Support\Role;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CareEarthUser extends Model
{
    protected $table = 'careearth_users';

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'show_performance',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'show_performance' => 'boolean',
        ];
    }

    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => Role::normalize($value ?? Role::VIEWER),
            set: fn (?string $value) => Role::normalize($value ?? Role::VIEWER),
        );
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
