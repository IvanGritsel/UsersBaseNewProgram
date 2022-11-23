<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
class ControllerMapping
{
    /**
     * @Required
     */
    public string $classMapping;
}
