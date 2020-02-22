<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use Ds\Vector;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\Section;
use Zlikavac32\QueryBuilder\StaticCompositeSection;

final class StaticCompositeSectionSpec extends ObjectBehavior
{

    public function let(Section $firstSection): void
    {
        $this->beConstructedWith($firstSection);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StaticCompositeSection::class);
    }

    public function it_should_join_chunks(Section $firstSection, Section $secondSection, Section $thirdSection): void
    {
        $firstSection->chunk()->willReturn('first');

        $this->chunk()->shouldReturn('first');

        $secondSection->chunk()->willReturn('second');
        $thirdSection->chunk()->willReturn('third');

        $this->append($secondSection, '||');
        $this->append($thirdSection, '&');

        $this->chunk()->shouldReturn('first||second&third');
    }

    public function it_should_join_parameters(Section $firstSection, Section $secondSection, Section $thirdSection
    ): void {
        $firstSection->chunk()->willReturn('first');
        $firstSection->markers()->willReturn(
            new Vector(
                [
                    ['foo', 2],
                ]
            )
        );

        $this->markers()->toArray()->shouldReturn(
            [
                ['foo', 2],
            ]
        );

        $secondSection->markers()->willReturn(
            new Vector(
                [
                    ['bar', 8],
                ]
            )
        );
        $secondSection->chunk()->willReturn('second');
        $thirdSection->markers()->willReturn(
            new Vector(
                [
                    ['baz', 10],
                ]
            )
        );
        $thirdSection->chunk()->willReturn('third');

        $this->append($secondSection, '||');
        $this->append($thirdSection, '&');

        $this->markers()->toArray()->shouldReturn(
            [
                ['foo', 2],
                ['bar', 15],
                ['baz', 24],
            ]
        );
    }
}
