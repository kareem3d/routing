<?php

use Routing\Filter;

try{

    foreach(Filter::all() as $filter)
    {
        if(! $code = $filter->code) continue;

        Route::filter($filter->getName(), function() use ($code)
        {
            $code = $code->getReadyCode();

            return eval($code);
        });
    }

// Catch exception because trying to get data from filters table will fail if it didn't exist.
}catch(Exception $e){}