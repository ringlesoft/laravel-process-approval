<?php

namespace Workbench\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasFactory;
    protected $guarded = [];

    public static function createSample()
    {
        return self::create(['name' => 'John Doe', 'email' => 'john@doe.com', 'password' => bcrypt('secret')]);
    }
}
