<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Pairs;
use function BenTools\QueryString\query_string;
use IteratorIterator;
use PHPUnit\Framework\TestCase;

class PairsTest extends TestCase
{

    public function testPairsWithoutDecoding()
    {
        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());
    }

    public function testPairsWithKeyDecoding()
    {
        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs, true));
        $pairs->rewind();
        $this->assertEquals('foo[bar]', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());

        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator((new Pairs($qs))->withDecodeKeys(true));
        $pairs->rewind();
        $this->assertEquals('foo[bar]', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());
    }

    public function testPairsWithValueDecoding()
    {
        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs, false, true));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz bat', $pairs->current());

        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator((new Pairs($qs))->withDecodeValues(true));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz bat', $pairs->current());
    }
}
