<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Chevere\Components\Cache\Cache;
use Chevere\Components\Cache\CacheKey;
use Chevere\Components\Router\Router;
use Chevere\Components\Routing\RoutingDescriptorsMaker;
use Chevere\Components\Spec\SpecMaker;
use Chevere\Components\Spec\SpecPath;
use Chevere\Components\VarExportable\VarExportable;
use function Chevere\Components\Filesystem\dirForPath;
use function Chevere\Components\Routing\routerForRoutingDescriptors;

require 'vendor/autoload.php';

set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');

$dir = dirForPath(__DIR__ . '/');
$routingDir = $dir->getChild('app/routing/');
$router = new Router;
foreach (['api-1', 'api-2-pub', 'api-2-admin'] as $group) {
    $routerForGroup = routerForRoutingDescriptors(
        (new RoutingDescriptorsMaker(
            $routingDir->getChild("$group/")
        ))->descriptors(),
        $group
    );
    foreach ($routerForGroup->routables()->getGenerator() as $routable) {
        $router = $router->withAddedRoutable($routable, $group);
    }
}
$cacheDir = $dir->getChild('cache/');
if ($cacheDir->exists()) {
    $cacheDir->removeContents();
}
$cacheRouteCollector = (new Cache($cacheDir->getChild('router/')))
    ->withAddedItem(
        new CacheKey('public'),
        new VarExportable($router->routeCollector())
    );
echo "Cached HTTP router\n";
$publicDir = $dir->getChild('public/');
$specDir = $publicDir->getChild('spec/');
$specPath = new SpecPath('/spec');
$specMaker = new SpecMaker($specPath, $specDir, $router);
echo 'Spec made at ' . $specDir->path()->absolute() . "\n";
