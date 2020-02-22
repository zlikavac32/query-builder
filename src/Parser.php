<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

interface Parser
{

    /**
     * @throws QueryBuilderException
     */
    public function parse(string $content, array $parameters): SectionMap;
}
