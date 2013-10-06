<?php namespace Kareem3d\Routing;

use Illuminate\Routing\Controllers\Controller;

abstract class RouterController extends Controller {

    /**
     * @return mixed
     */
    public function routeToLink()
    {
        $link = Link::getCurrent();

        $action = $link->getCurrentAction();

        $parameters = $link->getParameters();

        return call_user_func_array(array($this, $action), $parameters);
    }
}