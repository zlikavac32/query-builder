<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Vector;
use FFI;
use FFI\Exception;

final class FFISqlParser implements Parser
{

    private const MIN_SUPPORTED_VERSION = 1;

    private FFIGateway $ffi;

    public function __construct(FFIGateway $ffi)
    {
        $this->ffi = $ffi;

        if ($this->ffi->apiVersion() !== self::MIN_SUPPORTED_VERSION) {
            throw new QueryBuilderException(sprintf('API version %d is not supported', $this->ffi->apiVersion()));
        }
    }

    public function parse(string $content, array $parameters): SectionMap
    {
        try {
            $parseResult = $this->ffi->parseResultNew();

            try {
                $parseStatus = $this->ffi->parse($content, strlen($content), $parseResult);

                if ($parseStatus !== FFIGateway::PARSE_OK) {
                    throw new FFIParserException(
                        $parseStatus, $this->ffi->parseStatusToMessage($parseStatus)
                    );
                }

                $sectionMap = new SectionMap();

                $toProcess = [
                    'modifiers' => StatementSection::MODIFIERS(),
                    'columns' => StatementSection::COLUMNS(),
                    'first_into' => StatementSection::FIRST_INTO(),
                    'tables' => StatementSection::TABLES(),
                    'where' => StatementSection::WHERE(),
                    'group_by' => StatementSection::GROUP_BY(),
                    'having' => StatementSection::HAVING(),
                    'order_by' => StatementSection::ORDER_BY(),
                    'limit' => StatementSection::LIMIT(),
                    'procedure' => StatementSection::PROCEDURE(),
                    'second_into' => StatementSection::SECOND_INTO(),
                    'flags' => StatementSection::FLAGS(),
                ];

                $currentParameter = 0;

                foreach ($toProcess as $structureProperty => $sectionEnum) {
                    $rawData = $parseResult->$structureProperty;

                    if ($rawData->chunk === null) {
                        continue;
                    }

                    $parameterMarkers = new Vector();

                    $placeholders = $rawData->placeholders;
                    $placeholdersLocations = $placeholders->locations;

                    for ($i = 0; $i < $placeholders->count; $i++) {
                        if (!isset($parameters[$currentParameter])) {
                            throw new QueryBuilderException('To few parameters provided');
                        }

                        $parameterMarkers->push([$placeholdersLocations[$i], $parameters[$currentParameter++]]);
                    }

                    $sectionMap->setSectionFor(
                        $sectionEnum, new FFIParserSection(
                            FFI::string($rawData->chunk, $rawData->len),
                            $parameterMarkers
                        )
                    );
                }

                if ($currentParameter < count($parameters)) {
                    throw new QueryBuilderException('To manny parameters provided');
                }

                return $sectionMap;
            } finally {
                $this->ffi->parseResultFree($parseResult);
            }
        } catch (Exception $e) {
            throw new QueryBuilderException('Issue with the FFI', $e);
        }
    }
}
