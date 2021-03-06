<?php
namespace Sta;

use Doctrine\Common\Annotations;
use Doctrine\Common\Annotations\Annotation;
use ReflectionClass as PhpReflectionClass;

/**
 * @author: Stavarengo
 */
class ReflectionClass
{

    /**
     * @var
     */
    private static $_reader;
    /**
     * @var array
     */
    private static $_cache = [
        'self' => [],
        'class' => [],
        'property' => [],
        'method' => [],
    ];
    protected $isDevelopment;
    /**
     * @var PhpReflectionClass
     */
    private $className;

    protected function __construct($className, $isDevelopment)
    {
        $this->className     = $className;
        $this->isDevelopment = $isDevelopment;
    }

    /**
     * @param $objOrClassName
     *
     * @return ReflectionClass
     */
    public static function factory($objOrClassName, $isDevelopment)
    {
        if (is_object($objOrClassName)) {
            $objOrClassName = get_class($objOrClassName);
        }

        if (!isset(self::$_cache['self'][$objOrClassName])) {
            self::$_cache['self'][$objOrClassName] = new self($objOrClassName, $isDevelopment);
        }

        return self::$_cache['self'][$objOrClassName];
    }

    /**
     * Gets the annotations applied to a class.
     *
     * @return Annotation[].
     */
    public function getClassAnnotations()
    {
        return $this->getReader()->getClassAnnotations($this->getRefClass());
    }

    /**
     * Gets a class annotation.
     *
     * @param string $annotationName
     *        The name of the annotation.
     *
     * @return Annotation
     *        The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getClassAnnotation($annotationName)
    {
        return $this->getReader()->getClassAnnotation($this->getRefClass(), $annotationName);
    }

    /**
     * Gets the annotations applied to a property.
     *
     * @param string $property
     *            The property name from which the annotations should be read.
     *
     * @return Annotation[]
     */
    public function getPropertyAnnotations($property)
    {
        return $this->getReader()->getPropertyAnnotations($this->getRefProperty($property));
    }

    /**
     * Gets a property annotation.
     *
     * @param string $property
     * @param string $annotationName
     *        The name of the annotation.
     *
     * @return Annotation
     *        The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getPropertyAnnotation($property, $annotationName)
    {
        return $this->getReader()->getPropertyAnnotation($this->getRefProperty($property), $annotationName);
    }

    /**
     * Gets the annotations applied to a method.
     *
     * @param string $method
     *        The name of the method from which the annotations should be read.
     *
     * @return Annotation[].
     */
    public function getMethodAnnotations($method)
    {
        return $this->getReader()->getMethodAnnotations($this->getRefMethod($method));
    }

    /**
     * Gets a method annotation.
     *
     * @param string $method
     * @param string $annotationName
     *        The name of the annotation.
     *
     * @return Annotation
     *        The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getMethodAnnotation($method, $annotationName)
    {
        return $this->getReader()->getMethodAnnotation($this->getRefMethod($method), $annotationName);
    }

    /**
     * @return PhpReflectionClass
     */
    public function getRefClass()
    {
        if (!isset(self::$_cache['class'][$this->className])) {
            self::$_cache['class'][$this->className] = new PhpReflectionClass($this->className);
        }

        return self::$_cache['class'][$this->className];
    }

    /**
     * @param $property
     *
     * @return \ReflectionProperty
     */
    public function getRefProperty($property)
    {
        if (!isset(self::$_cache['property'][$this->className][$property])) {
            self::$_cache['property'][$this->className][$property] = new \ReflectionProperty(
                $this->className, $property
            );
        }

        return self::$_cache['property'][$this->className][$property];
    }

    /**
     * @param $method
     *
     * @return \ReflectionMethod
     */
    public function getRefMethod($method)
    {
        if (!isset(self::$_cache['method'][$this->className][$method])) {
            self::$_cache['method'][$this->className][$method] = new \ReflectionMethod($this->className, $method);
        }

        return self::$_cache['method'][$this->className][$method];
    }

    /**
     * @return Annotations\CachedReader
     */
    private function getReader()
    {
        if (!self::$_reader) {
            self::$_reader = new Annotations\AnnotationReader;
            $conf          = \Sta\Module::getServiceLocator()->get('Configuration');
            $staConf       = $conf['sta'];
            $cache         = $staConf['ReflectionClass']['cache'];
            if ($cache && !is_object($cache)) {
                if ($cache == 'filesystem') {
                    $cache = new \Doctrine\Common\Cache\FilesystemCache(
                        $staConf['ReflectionClass']['cacheDir'],
                        'sta.ref.class'
                    );
                } else {
                    $cache = new $cache;
                }
            }
            self::$_reader = new Annotations\CachedReader(
                new Annotations\IndexedReader(self::$_reader),
                $cache,
                $this->isDevelopment
            );
        }

        return self::$_reader;
    }

}
