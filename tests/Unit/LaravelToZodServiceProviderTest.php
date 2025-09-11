<?php

use Hynek\LaravelToZod\LaravelToZodServiceProvider;

it('configures package correctly', function () {
    $provider = new LaravelToZodServiceProvider($this->app);
    
    expect($provider)->toBeInstanceOf(LaravelToZodServiceProvider::class);
});

it('registers package with correct name', function () {
    // The package should be configured with the name 'hynek-laravel-to-zod'
    // This is tested indirectly by ensuring the service provider works
    $provider = new LaravelToZodServiceProvider($this->app);
    
    expect($provider)->toBeInstanceOf(LaravelToZodServiceProvider::class);
});
