<?php

namespace RingleSoft\LaravelProcessApproval\Facades;

use Illuminate\Support\Facades\Facade;

class ProcessApproval extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RingleSoft\LaravelProcessApproval\ProcessApproval::class;
    }
}
