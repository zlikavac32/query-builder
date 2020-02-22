<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Throwable;

class FFIParserException extends QueryBuilderException
{

    public const PARSE_ERROR_INVALID_ARGUMENT = 32001;
    public const PARSE_INVALID_SYNTAX = 32002;

    private string $textCode;
    private int $internalCode;

    public function __construct(int $internalCode, string $textCode, Throwable $previous = null)
    {
        parent::__construct(sprintf("Parsing failed with %s", $textCode), $previous);
        $this->textCode = $textCode;
        $this->internalCode = $internalCode;
    }

    public function textCode(): string
    {
        return $this->textCode;
    }

    public function internalCode(): int
    {
        return $this->internalCode;
    }
}
