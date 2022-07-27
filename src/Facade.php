<?php

namespace mam4dali\LaravelWpApi;

use mam4dali\LaravelWpApi\WpApi as WordpressApi;

class Facade extends \Illuminate\Support\Facades\Facade
{

    protected static function getFacadeAccessor()
    {
        return WordpressApi::class;
    }

}
