<?php
/**
 * irmo Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Annotation;

abstract class AbstractAnnotation implements AnnotationInterface
{
    /**
     * Returns the string class name of the GraphObject or subclass.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
} 