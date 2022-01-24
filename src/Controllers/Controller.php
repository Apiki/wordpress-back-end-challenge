<?php

namespace FavoritePostPlugin\Controllers;

abstract class Controller
{
    private $namespace;

    abstract function registerRoutes();
}
