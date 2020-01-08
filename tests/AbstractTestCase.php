<?php

namespace Novius\ScoutElastic\Test;

use Novius\ScoutElastic\ScoutElasticServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class AbstractTestCase extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $defaultConfig = include __DIR__.'/../config/scout_elastic.php';
        app('config')->set('scout_elastic', $defaultConfig);
    }

    protected function getPackageProviders($app)
    {
        return [
            ScoutElasticServiceProvider::class,
        ];
    }
}
