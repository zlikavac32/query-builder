<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Throwable;

class ProfilerParser implements Parser
{

    private Parser $parser;
    private array $traces = [];

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(string $content, array $parameters): SectionMap
    {
        $now = microtime(true);

        try {
            $sectionMap = $this->parser->parse($content, $parameters);

            $duration = microtime(true) - $now;

            $this->traces[] = [
                'sql' => $content,
                'duration' => $duration,
                'exception' => false,
            ];

            return $sectionMap;
        } catch (Throwable $e) {
            $duration = microtime(true) - $now;

            $this->traces[] = [
                'sql' => $content,
                'duration' => $duration,
                'exception' => true,
            ];

            throw $e;
        }
    }

    public function traces(): array
    {
        return $this->traces;
    }

    public function reset(): void
    {
        $this->traces = [];
    }
}
