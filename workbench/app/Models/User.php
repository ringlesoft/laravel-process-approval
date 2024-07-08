<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasFactory;
    protected $guarded = [];

    public static function createSample()
    {
        return self::query()->updateOrCreate(['email' => 'john@doe.com'],['name' => 'John Doe', 'email' => 'john@doe.com', 'password' => bcrypt('secret')]);
    }
}
