<?php

namespace RingleSoft\LaravelProcessApproval\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface UserResolver
{
    /**
     * Resolve the user acting on approvals (submitting, approving, rejecting, returning or discarding).
     *
     * The returned user is trusted as the actor. Never return a user taken from request input
     * (e.g. `user_id`) without first authorizing the authenticated user to act on their behalf.
     * @param Request|null $request
     * @return Authenticatable|null
     */
    public function resolve(Request|null $request = null): Authenticatable|null;
}
