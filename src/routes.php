<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Routing\Link;

try{

    // Extract uri from the url
    $requestUri = urldecode(Request::path());

    // Get request type(GET, POST, PUT or DELETE)
    $requestType = Request::server('REQUEST_METHOD');

    // Get link by request type and uri
    $link = Link::findByUriAndRequestType( $requestUri, $requestType );

    // If link exists
    if($link)
    {
        // Save current link in the ioc container
        App::instance('CurrentLink', $link);

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

        Route::$requestType($link->getUri(), $array);
    }
}catch(Exception $e){}