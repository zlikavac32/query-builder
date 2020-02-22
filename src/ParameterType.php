<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Zlikavac32\Enum\Enum;

/**
 * @method static ParameterType T_BOOL
 * @method static ParameterType T_NULL
 * @method static ParameterType T_INTEGER
 * @method static ParameterType T_STRING
 * @method static ParameterType T_LARGE_OBJECT
 * @method static ParameterType T_ARRAY_INT
 * @method static ParameterType T_ARRAY_STRING
 */
abstract class ParameterType extends Enum
{

}
