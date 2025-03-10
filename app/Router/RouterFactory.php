<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        if (PHP_SAPI === 'cli') {
            $router[] = new Nette\Application\Routers\CliRouter();
            $router->addRoute('<presenter>/<action>[/<id>]', 'Dashboard:default');
        } else {
            $router->addRoute('admin/<presenter>/<action>[/<id>]',
                ['module'=>'Admin','presenter'=>'Dashboard','action'=>'default'],
            );

            $router->addRoute('reporting/<presenter>/<action>[/<id>]',
                ['module'=>'Reporting','presenter'=>'Dashboard','action'=>'default'],
            );

            $router->addRoute('<presenter>/<action>[/<id>]', 'Dashboard:default');
        }

        return $router;
    }
}
