<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;
use Ds\Vector;

final class StaticCompositeSection implements CompositeSection
{

    /**
     * @var Section[]|string[]|Sequence
     */
    private Sequence $sections;

    public function __construct(Section $section)
    {
        $this->sections = new Vector([$section]);
    }

    public function append(Section $section, string $glue): void
    {
        $this->sections->push($glue, $section);
    }

    public function chunk(): string
    {
        return $this->sections
            ->map(fn($item) => $item instanceof Section ? $item->chunk() : $item)
            ->join('');
    }

    public function markers(): Sequence
    {
        $currentOffset = 0;

        return $this->sections
            ->map(
                function ($section) use (&$currentOffset): ?Sequence {
                    if ($section instanceof Section) {
                        $ret = $section->markers()->map(
                            fn(array $marker) => [$marker[0], $marker[1] + $currentOffset]
                        );

                        $currentOffset += strlen($section->chunk());

                        return $ret;
                    }

                    $currentOffset += strlen($section);

                    return null;
                }
            )
            ->filter(fn($v) => $v !== null)
            ->reduce(
                function (Sequence $all, Sequence $markers): Sequence {
                    foreach ($markers as $marker) {
                        $all->push($marker);
                    }

                    return $all;
                }, new Vector()
            );
    }
}
