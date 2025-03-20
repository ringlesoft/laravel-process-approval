<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasFactory, HasRoles, HasPermissions;
    protected $guarded = [];

    public static function createSample()
    {
        $user = self::query()->updateOrCreate(['email' => 'hod@test.com'],['name' => 'John Doe', 'email' => 'hod@doe.com', 'password' => bcrypt('secret')]);
        $user->roles()->sync([Role::find(1)->id]);
        return $user;
    }
    public static function createMultiple()
    {
        $roles = Role::all();
        $user = self::query()->updateOrCreate(['email' => 'hod@test.com'],['name' => 'John Doe', 'email' => 'hod@doe.com', 'password' => bcrypt('secret')]);
        $user2 = self::query()->updateOrCreate(['email' => 'accountant@test.com'],['name' => 'John Doe', 'email' => 'accountant@doe.com', 'password' => bcrypt('secret')]);
        $user3 = self::query()->updateOrCreate(['email' => 'dirctor@test.com'],['name' => 'John Doe', 'email' => 'dirctor@doe.com', 'password' => bcrypt('secret')]);
        $user4 = self::query()->updateOrCreate(['email' => 'ceo@test.com'],['name' => 'John Doe', 'email' => 'ceo@doe.com', 'password' => bcrypt('secret')]);
        $user->roles()->sync([$roles[0]]);
        $user2->roles()->sync([$roles[1]]);
        $user3->roles()->sync([$roles[2]]);
        $user4->roles()->sync([$roles[3]]);
        return $user;
    }
}
