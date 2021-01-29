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
use function Chevere\Components\Filesystem\dirForPath;
use Chevere\Components\Router\Router;
use function Chevere\Components\Router\Routing\routerForRoutingDescriptors;
use Chevere\Components\Router\Routing\RoutingDescriptorsMaker;
use Chevere\Components\Spec\SpecMaker;
use Chevere\Components\VarStorable\VarStorable;

require 'vendor/autoload.php';

set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');

$specDir = dirForPath(__DIR__ . '/');
$routingDir = $specDir->getChild('app/routing/');
$router = new Router();
foreach (['api-v1', 'api-v2'] as $group) {
    $routerForGroup = routerForRoutingDescriptors(
        (new RoutingDescriptorsMaker($group))
            ->withDescriptorsFor($routingDir->getChild("${group}/"))
            ->descriptors()
    );
    foreach ($routerForGroup->routables()->getGenerator() as $routable) {
        $router = $router->withAddedRoutable($routable, $group);
    }
}
// {adds extend routing}
// {adds vendor routing}
$cacheDir = $specDir->getChild('volumes/cache/');
if ($cacheDir->exists()) {
    $cacheDir->removeContents();
}
(new Cache($cacheDir->getChild('router/')))
    ->withPut(
        new CacheKey('public'),
        new VarStorable($router->routeCollector())
    );
echo "Cached HTTP router\n";
$publicDir = $specDir->getChild('volumes/public/');
$specDir = $publicDir->getChild('spec/');
$specMaker = new SpecMaker(
    dirForPath('/spec/'),
    $specDir,
    $router
);
echo 'Spec made at ' . $specDir->path()->toString() . "\n";
