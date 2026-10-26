<?php

namespace RingleSoft\LaravelProcessApproval\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RingleSoft\LaravelProcessApproval\Contracts\UserResolver;

/**
 * Resolves the currently authenticated user.
 * Extend this class (or implement UserResolver) and set `process_approval.user_resolver` to customize.
 */
class DefaultUserResolver implements UserResolver
{
    public function resolve(Request|null $request = null): Authenticatable|null
    {
        return $request ? $request->user() : Auth::user();
    }
}
