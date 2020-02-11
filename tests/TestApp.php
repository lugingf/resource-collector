<?php
declare(strict_types=1);

namespace RMT\ResourceCollector;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use RMS\ResourceCollector\Application;

class TestApp extends Application
{
    protected function isDebug()
    {
        // In order to disable DI-container cache - it makes it possible to define separate definitions for every test.
        return true;
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        parent::configureContainer($builder);
        $builder->addDefinitions(__DIR__ . '/config/di.php');
    }

    protected function initDb(): void
    {
        $capsule = new Manager();
        $capsule->addConnection(
            [
                'driver'    => 'sqlite',
                'database'  => __DIR__ . '/../db.sqlite3',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]
        );
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
