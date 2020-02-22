<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use Ds\Vector;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\Section;
use Zlikavac32\QueryBuilder\SectionWithinParenthesis;

final class SectionWithinParenthesisSpec extends ObjectBehavior
{

    public function let(Section $section): void
    {
        $this->beConstructedWith($section);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SectionWithinParenthesis::class);
    }

    public function it_should_return_modified_chunk(Section $section): void
    {
        $section->chunk()->willReturn('bar');

        $this->chunk()->shouldReturn('(bar)');
    }

    public function it_should_return_modified_markers(Section $section): void
    {
        $section->markers()->willReturn(
            new Vector(
                [
                    ['foo', 10],
                    ['bar', 12],
                ]
            )
        );

        $this->markers()->toArray()->shouldReturn(
            [
                ['foo', 11],
                ['bar', 13],
            ]
        );
    }
}
