<?php
namespace WPDev\Docs;

use ReflectionMethod;

class ClassParser extends \ReflectionClass{
    /**
     * @param null $filter Filter the results to include only methods with certain attributes.
     *
     * @return \WPDev\Docs\MethodParser[]
     */
    public function getMethods($filter = null)
    {
        return array_map(function($method) {
            return new MethodParser($this->name, $method->name);
        }, parent::getMethods($filter));
    }

    /**
     * @return \WPDev\Docs\MethodParser[]
     */
    public function getPublicMethods()
    {
        return $this->getMethods(ReflectionMethod::IS_PUBLIC);
    }

    /**
     * @return \WPDev\Docs\MethodParser[]
     */
    public function getProtectedMethods()
    {
        return $this->getMethods(ReflectionMethod::IS_PROTECTED);
    }

    /**
     * @return \WPDev\Docs\MethodParser[]
     */
    public function getPrivateMethods()
    {
        return $this->getMethods(ReflectionMethod::IS_PRIVATE);
    }
}