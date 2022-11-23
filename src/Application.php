<?php

namespace App;

use App\Listener\Listener;
use Doctrine\Common\Annotations\AnnotationRegistry;

class Application
{
    public static function runApi(): void
    {
        define('ROOT_DIR', __DIR__ . '\\');
        define('CONFIG_DIR', __DIR__ . '/../resource/config/');
        AnnotationRegistry::registerLoader('class_exists');
        $listener = new Listener();
        $listener->startListening();
    }

    public static function run(): void
    {

    }
}
