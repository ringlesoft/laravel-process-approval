<?php

namespace RingleSoft\LaravelProcessApproval\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RingleSoft\LaravelProcessApproval\ProcessApproval
 * @mixin \RingleSoft\LaravelProcessApproval\ProcessApproval
 */
class ProcessApproval extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RingleSoft\LaravelProcessApproval\ProcessApproval::class;
    }
}
