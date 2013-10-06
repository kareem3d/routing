<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Kareem3d\Routing\Link;

try{

    // Get request type(GET, POST, PUT or DELETE)
    $requestType = strtolower(Request::server('REQUEST_METHOD'));

    // Get link by request type and uri
    $link = Link::getCurrent( $requestType );

    // If link exists
    if($link)
    {
        // Get link action
        $action = $link->getCurrentAction();

        // Use the routeToLinkInfo method to call the link action with type and passing
        // the link parameters
        $array['uses'] = $link->getController() . '@routeToLink';

        // Assign before filters if exists
        if($link->hasBeforeFilters())
        {
            $array['before'] = $link->getConcatBeforeFilters();
        }

        // Assign after filters if exists
        if($link->hasAfterFilters())
        {
            $array['after'] = $link->getConcatAfterFilters();
        }

        Route::$requestType($link->url->getUri(), $array);
    }

}catch(Exception $e){}