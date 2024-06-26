<?php

// Define app routes
use CM3_Lib\util\PermEvent;
use CM3_Lib\Middleware\PermCheckEventPerm;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

return function (App $app, $container) {
    $accessPerm = $container->get(PermCheckEventPerm::class);
    $app->get('/AdminUser/GetPerms', \CM3_Lib\Action\AdminUser\GetPerms::class);
    $app->group(
        '/AdminUser',
        function (RouteCollectorProxy $app) {
            $app->get('', \CM3_Lib\Action\AdminUser\Search::class);
            $app->post('', \CM3_Lib\Action\AdminUser\Create::class);
            $app->get('/{id}', \CM3_Lib\Action\AdminUser\Read::class);
            $app->post('/{id}', \CM3_Lib\Action\AdminUser\Update::class);
            $app->delete('/{id}', \CM3_Lib\Action\AdminUser\Delete::class);
        }
    )->add($accessPerm->withAllowedPerms(array(PermEvent::Manage_Users())));
};
