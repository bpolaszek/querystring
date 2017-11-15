<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Parser\NativeParser;
use PHPUnit\Framework\TestCase;

class NativeParserTest extends TestCase
{

    public function testParser()
    {
        $parser = new NativeParser();
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $parser->parse('foo=bar&baz=bat'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $parser->parse('?foo=bar&baz=bat'));
        $this->assertEquals(['foo' => 'baz'], $parser->parse('foo=bar&foo=baz'));
    }
}
