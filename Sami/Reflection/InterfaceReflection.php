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

/**
 * Reflection for interface
 */
class InterfaceReflection extends OoItemReflection
{

    public function getDescriptor()
    {
        return 'interface';
    }

    public function isInterface()
    {
        return true;
    }

    static public function fromArray(Project $project, $array)
    {
        $class = new self($array['name'], $array['line']);
        $class->shortDesc = $array['short_desc'];
        $class->longDesc = $array['long_desc'];
        $class->hint = $array['hint'];
        $class->tags = $array['tags'];
        $class->namespace = $array['namespace'];
        $class->hash = $array['hash'];
        $class->file = $array['file'];
        $class->modifiers = $array['modifiers'];
        $class->aliases = $array['aliases'];
        $class->errors = $array['errors'];
        $class->parent = $array['parent'];
        $class->interfaces = $array['interfaces'];
        $class->constants = $array['constants'];

        $class->setProject($project);

        foreach ($array['methods'] as $method) {
            $method = MethodReflection::fromArray($project, $method);
            $method->setClass($class);
            $class->addMethod($method);
        }

        foreach ($array['properties'] as $property) {
            $property = PropertyReflection::fromArray($project, $property);
            $property->setClass($class);
            $class->addProperty($property);
        }

        foreach ($array['constants'] as $constant) {
            $constant = ConstantReflection::fromArray($project, $constant);
            $constant->setClass($class);
            $class->addConstant($constant);
        }

        return $class;
    }

}
