<?php

namespace Nexus\Core;

abstract class Package
{
    /**
     * Register package services
     */
    public function register(Application $app): void
    {
        //
    }

    /**
     * Bootstrap package services
     */
    public function boot(Application $app): void
    {
        //
    }

    /**
     * Get package routes
     */
    public function routes(): ?string
    {
        return null;
    }

    /**
     * Get package views path
     */
    public function views(): ?string
    {
        return null;
    }

    /**
     * Get package config files
     */
    public function config(): array
    {
        return [];
    }
}
