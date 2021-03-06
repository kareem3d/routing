<?php

use Illuminate\Support\Facades\Route;
use Kareem3d\Routing\Filter;

try{

    foreach(Filter::all() as $filter)
    {
        if(! $code = $filter->code) continue;

        Route::filter($filter->getName(), function() use ($code)
        {
            return $code->evaluate();
        });
    }

// Catch exception because trying to get data from filters table will fail if it didn't exist.
}catch(Exception $e){}