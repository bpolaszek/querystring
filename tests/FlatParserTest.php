<?php

declare(strict_types=1);

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Parser\FlatParser;
use PHPUnit\Framework\TestCase;

use function BenTools\QueryString\query_string;

class FlatParserTest extends TestCase
{
    public function testFlatQueryParser(): void
    {
        $qs = query_string('topic=foo&topic=bar&topic=foo%20bar&foo=bar&hi', new FlatParser());
        $expected = [
            'topic' => ['foo', 'bar', 'foo bar'],
            'foo' => 'bar',
            'hi' => null,
        ];
        $this->assertEquals($expected, $qs->getParams());
    }
}
