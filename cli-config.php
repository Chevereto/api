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

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;

require_once 'vendor/autoload.php';

$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/src/Entities']);
$connectionParams = [
    'dbname' => 'chevereto-4',
    'user' => 'root',
    'password' => 'root',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];
$entityManager = \Doctrine\ORM\EntityManager::create($connectionParams, $config);

return ConsoleRunner::createHelperSet($entityManager);
