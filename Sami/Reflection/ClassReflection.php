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
 * Reflection for class
 */
class ClassReflection extends OoItemReflection
{

    public function getDescriptor()
    {
        return 'class';
    }

    public function isInterface()
    {
        return false;
    }

}
