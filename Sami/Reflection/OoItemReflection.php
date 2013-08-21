<?php

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Reflection;

use Sami\Project;

/**
 * Common behavior for Class, Interface and Trait
 */
abstract class OoItemReflection extends Reflection
{
    protected $project;
    protected $hash;
    protected $namespace;
    protected $modifiers;
    protected $properties = array();
    protected $methods = array();
    protected $interfaces = array();
    protected $constants = array();
    protected $parent;
    protected $file;
    protected $projectClass = true;
    protected $aliases = array();
    protected $errors = array();

    public function __toString()
    {
        return $this->name;
    }

    public function getClass()
    {
        return $this;
    }

    abstract public function getDescriptor();

    public function isProjectClass()
    {
        return $this->projectClass;
    }

    public function isPhpClass()
    {
        try {
            $r = new \ReflectionClass($this->name);

            return $r->isInternal();
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    public function setName($name)
    {
        parent::setName(ltrim($name, '\\'));
    }

    public function getShortName()
    {
        if (false !== $pos = strrpos($this->name, '\\')) {
            return substr($this->name, $pos + 1);
        }

        return $this->name;
    }

    public function isAbstract()
    {
        return self::MODIFIER_ABSTRACT === (self::MODIFIER_ABSTRACT & $this->modifiers);
    }

    public function isFinal()
    {
        return self::MODIFIER_FINAL === (self::MODIFIER_FINAL & $this->modifiers);
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = ltrim($namespace, '\\');
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public function addProperty(PropertyReflection $property)
    {
        $this->properties[$property->getName()] = $property;
        $property->setClass($this);
    }

    public function getProperties($deep = false)
    {
        if (false === $deep) {
            return $this->properties;
        }

        $properties = array();
        if ($this->getParent()) {
            foreach ($this->getParent()->getProperties(true) as $name => $property) {
                $properties[$name] = $property;
            }
        }

        foreach ($this->properties as $name => $property) {
            $properties[$name] = $property;
        }

        return $properties;
    }

    /*
     * Can be any iterator (so that we can lazy-load the properties)
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    public function addConstant(ConstantReflection $constant)
    {
        $this->constants[$constant->getName()] = $constant;
        $constant->setClass($this);
    }

    public function getConstants($deep = false)
    {
        if (false === $deep) {
            return $this->constants;
        }

        $constants = array();
        if ($this->getParent()) {
            foreach ($this->getParent()->getConstants(true) as $name => $constant) {
                $constants[$name] = $constant;
            }
        }

        foreach ($this->constants as $name => $constant) {
            $constants[$name] = $constant;
        }

        return $constants;
    }

    public function setConstants($constants)
    {
        $this->constants = $constants;
    }

    public function addMethod(MethodReflection $method)
    {
        $this->methods[$method->getName()] = $method;
        $method->setClass($this);
    }

    public function getMethod($name)
    {
        return isset($this->methods[$name]) ? $this->methods[$name] : false;
    }

    public function getParentMethod($name)
    {
        if ($this->getParent()) {
            foreach ($this->getParent()->getMethods() as $n => $method) {
                if ($name == $n) {
                    return $method;
                }
            }
        }

        foreach ($this->getInterfaces(true) as $interface) {
            foreach ($interface->getMethods() as $n => $method) {
                if ($name == $n) {
                    return $method;
                }
            }
        }
    }

    public function getMethods($deep = false)
    {
        if (false === $deep) {
            return $this->methods;
        }

        $methods = array();
        if ($this->isInterface()) {
            foreach ($this->getInterfaces() as $interface) {
                foreach ($interface->getMethods() as $name => $method) {
                    $methods[$name] = $method;
                }
            }
        }

        if ($this->getParent()) {
            foreach ($this->getParent()->getMethods() as $name => $method) {
                $methods[$name] = $method;
            }
        }

        foreach ($this->methods as $name => $method) {
            $methods[$name] = $method;
        }

        return $methods;
    }

    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    public function addInterface($interface)
    {
        $this->interfaces[$interface] = $interface;
    }

    public function getInterfaces($deep = false)
    {
        $interfaces = array();
        foreach ($this->interfaces as $interface) {
            $interfaces[] = $this->project->getClass($interface);
        }

        if (false === $deep) {
            return $interfaces;
        }

        $allInterfaces = $interfaces;
        foreach ($interfaces as $interface) {
            $allInterfaces = array_merge($allInterfaces, $interface->getInterfaces());
        }

        if ($parent = $this->getParent()) {
            $allInterfaces = array_merge($allInterfaces, $parent->getInterfaces());
        }

        return $allInterfaces;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent($deep = false)
    {
        if (!$this->parent) {
            return $deep ? array() : null;
        }

        $parent = $this->project->getClass($this->parent);

        if (false === $deep) {
            return $parent;
        }

        return array_merge(array($parent), $parent->getParent(true));
    }

    abstract public function isInterface();

    public function isException()
    {
        $parent = $this;
        while ($parent = $parent->getParent()) {
            if ('Exception' == $parent->getName()) {
                return true;
            }
        }

        return false;
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function toArray()
    {
        return array(
            'name'         => $this->name,
            'line'         => $this->line,
            'short_desc'   => $this->shortDesc,
            'long_desc'    => $this->longDesc,
            'hint'         => $this->hint,
            'tags'         => $this->tags,
            'namespace'    => $this->namespace,
            'file'         => $this->file,
            'hash'         => $this->hash,
            'parent'       => $this->parent,
            'modifiers'    => $this->modifiers,
            'aliases'      => $this->aliases,
            'errors'       => $this->errors,
            'interfaces'   => $this->interfaces,
            'properties'   => array_map(function ($property) { return $property->toArray(); }, $this->properties),
            'methods'      => array_map(function ($method) { return $method->toArray(); }, $this->methods),
            'constants'    => array_map(function ($constant) { return $constant->toArray(); }, $this->constants),
        );
    }

}
