<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use Ds\Vector;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\FFIParserSection;

final class FFIParserSectionSpec extends ObjectBehavior
{

    public function it_is_initializable(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(FFIParserSection::class);
    }

    public function it_should_return_provided_chunk(): void
    {
        $this->beConstructedWith('foo');
        $this->chunk()->shouldReturn('foo');
    }

    public function it_should_return_empty_markers_when_none_provided(): void
    {
        $this->beConstructedWith('foo');
        $this->markers()->isEmpty()->shouldReturn(true);
    }

    public function it_should_return_provided_markers(): void
    {
        $this->beConstructedWith(
            'foo', new Vector(
                [
                    [2, 'bar'],
                    [5, 'baz'],
                ]
            )
        );

        $this->markers()->toArray()->shouldReturn(
            [
                ['bar', 2],
                ['baz', 5],
            ]
        );
    }
}
