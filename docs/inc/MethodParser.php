<?php

namespace WPDev\Docs;

use phpDocumentor\Reflection\DocBlockFactory;

class MethodParser extends \ReflectionMethod
{
    protected $docblock;

    public function __construct($class, $method)
    {
        parent::__construct($class, $method);
        if ($this->getDocComment()) {
            $factory        = DocBlockFactory::createInstance();
            $this->docblock = $factory->create($this->getDocComment());
        }
    }

    public function getDescription()
    {
        if ( ! $this->docblock) {
            return '';
        }

        return (string)$this->docblock->getDescription();
    }

    public function getReturnDescription()
    {
        if (!$this->docblock) {
            return '';
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Return_ $return */
        $return = $this->docblock->getTagsByName('return');
        if ($return) {
            $return = $return[0];
            return (string) $return->getDescription();
        }

        return '';
    }

    public function getReturnType()
    {
        $type = parent::getReturnType();

        if (is_null($type) && $this->docblock) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Return_ $return */
            $return = $this->docblock->getTagsByName('return');

            if ($return) {
                $return = $return[0];
            }

            $type = $return ? (string) $return->getType() : $type;
        }

        return $type;
    }

    public function getSummary()
    {
        if ( ! $this->docblock) {
            return '';
        }

        return $this->docblock->getSummary();
    }

    public function getParameters()
    {
        $params = parent::getParameters();

        $docParams = $this->docblock ? $this->docblock->getTagsByName('param') : [];

        $args = [];

        foreach ($params as $param) {
            $data = [];

            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $doc */
            $doc  = $this->getParamDocByName($param->getName());

            $data['name'] = $param->getName();

            // description
            if ($doc && $doc->getDescription()) {
                $data['description'] = (string)$doc->getDescription();
            }

            // handle the type, maybe override with type hint
            if ($doc && $doc->getType()) {
                $data['type'] = (string)$doc->getType();
            }
            if ($param->getType()) {
                $data['type'] = (string)$param->getType();
            }

            // default val
            if ($param->isOptional()) {

                $data['default'] = $param->getDefaultValue();
            }

            $args[] = $data;
        }

        return $args;
    }

    public function getVisibility()
    {
        if ($this->isPublic()) {
            return 'public';
        }
        if ($this->isProtected()) {
            return 'protected';
        }
        if ($this->isPrivate()) {
            return 'private';
        }
    }

    /**
     * Checks to see if there is an @return tag
     *
     * @return bool
     */
    public function hasReturnTag()
    {
        if (!$this->docblock) {
            return false;
        }

        return count($this->docblock->getTagsByName('return')) > 0;
    }

    protected function getParamDocByName($name)
    {
        $docParams = $this->docblock ? $this->docblock->getTagsByName('param') : [];

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $docParam */
        foreach ($docParams as $key => $docParam) {
            if ($docParam->getVariableName() === $name) {
                return $docParams[$key];
            }
        }

        return null;
    }
}