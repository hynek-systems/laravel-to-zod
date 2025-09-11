<?php

namespace Hynek\LaravelToZod;

use Hynek\HynekModuleTools\HynekModuleToolsServiceProvider;
use Hynek\HynekModuleTools\Package;

class LaravelToZodServiceProvider extends HynekModuleToolsServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package->name('hynek-laravel-to-zod');
    }
}
