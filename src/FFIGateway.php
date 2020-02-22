<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use FFI\CData;

interface FFIGateway
{

    public const PARSE_OK = 32000;

    public function parseStatusToMessage(int $parseStatus): string;

    public function parseResultNew(): CData;

    public function parse(string $sql, int $len, CData $parseResult): int;

    public function parseResultFree(CData $parseResult): void;

    public function apiVersion(): int;
}
