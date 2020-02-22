<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use LogicException;
use Throwable;

class QueryBuilderException extends LogicException
{

    public function __construct($message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
