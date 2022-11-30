<?php

namespace App;

use App\Dispatcher\Dispatcher;
use App\Listener\Listener;
use Doctrine\Common\Annotations\AnnotationRegistry;

class Application
{
    public static function runApi(): void
    {
        define('ROOT_DIR', __DIR__ . '/');
        AnnotationRegistry::registerLoader('class_exists');
        $listener = new Listener();
        $listener->startListening();
    }

    public static function run(string $request): string
    {
        define('ROOT_DIR', __DIR__ . '/');
        AnnotationRegistry::registerLoader('class_exists');
        $dispatcher = new Dispatcher();
        return $dispatcher->dispatchRequestNoHttp($request);
    }
}
