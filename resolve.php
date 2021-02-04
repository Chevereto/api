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

use Chevere\Components\Action\ActionRunner;
use Chevere\Components\Cache\Cache;
use Chevere\Components\Cache\CacheKey;
use function Chevere\Components\Filesystem\dirForPath;
use Chevere\Components\Router\RouterDispatcher;
use Chevere\Interfaces\Parameter\StringParameterInterface;

foreach (['vendor/autoload.php', '../vendor/autoload.php'] as $autoload) {
    if (stream_resolve_include_path($autoload)) {
        require $autoload;

        break;
    }
}
set_exception_handler('Chevere\Components\ThrowableHandler\consoleHandler');
set_error_handler('Chevere\Components\ThrowableHandler\errorsAsExceptions');
$cacheDir = dirForPath(__DIR__ . '/')->getChild('cache/')->getChild('router/');
$routeCollector = (new Cache($cacheDir))
    ->get(new CacheKey('public'))->var();
$dispatcher = new RouterDispatcher($routeCollector);
$routed = $dispatcher->dispatch('POST', '/api/2/pub/image/binary/');
$arguments = $routed->arguments();
$controller = $routed->getController()->withSetUp();
/**
 * @var StringParameterInterface $parameter
 */
foreach ($controller->parameters()->getGenerator() as $parameter) {
    if ($parameter->hasAttribute('tryFiles')) {
        $arguments[$parameter->name()] = serialize([
            'tmp_name' => __FILE__,
        ]);
    }
}
$runner = new ActionRunner($controller);
$executed = $runner->execute($arguments);
if ($executed->hasThrowable()) {
    throw $executed->throwable();
}
echo json_encode($executed->data());
