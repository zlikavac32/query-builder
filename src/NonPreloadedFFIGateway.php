<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use FFI;
use FFI\CData;

final class NonPreloadedFFIGateway implements FFIGateway
{

    private FFI $ffi;

    public function __construct(string $headerFilePath = __DIR__ . '/../tsqlp.h')
    {
        if (!extension_loaded('ffi')) {
            throw new QueryBuilderException('FFI extension must be enabled to use this class');
        }

        $this->ffi = FFI::load($headerFilePath);
    }

    public function parseResultNew(): CData
    {
        return $this->ffi->tsqlp_parse_result_new();
    }

    public function parse(string $sql, int $len, CData $parseResult): int
    {
        return $this->ffi->tsqlp_parse($sql, $len, $parseResult);
    }

    public function parseResultFree(CData $parseResult): void
    {
        $this->ffi->tsql_parse_result_free($parseResult);
    }

    public function parseStatusToMessage(int $parseStatus): string
    {
        return $this->ffi->tsqlp_parse_status_to_message($parseStatus);
    }

    public static function createDefault(): FFIGateway
    {
        return new NonPreloadedFFIGateway();
    }

    public function apiVersion(): int
    {
        return $this->ffi->tsqlp_api_version();
    }
}
