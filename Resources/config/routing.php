<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->add('AcmeSshBundle_homepage', new Route('/hello/{name}', array(
    '_controller' => 'AcmeSshBundle:Default:index',
)));

return $collection;
