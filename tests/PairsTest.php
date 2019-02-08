<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Pairs;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use function BenTools\QueryString\query_string;

class PairsTest extends TestCase
{

    public function testPairsWithoutDecoding()
    {
        $qs = (string) query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());
    }

    public function testPairsWithKeyDecoding()
    {
        $qs = (string) query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs, true));
        $pairs->rewind();
        $this->assertEquals('foo[bar]', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());

        $qs = (string) query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator((new Pairs($qs))->withDecodeKeys(true));
        $pairs->rewind();
        $this->assertEquals('foo[bar]', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());
    }

    public function testPairsWithValueDecoding()
    {
        $qs = (string) query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator(new Pairs($qs, false, true));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz bat', $pairs->current());

        $qs = (string) query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator((new Pairs($qs))->withDecodeValues(true));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz bat', $pairs->current());
    }

    public function testPairsWithDifferentSeparator()
    {
        $qs = 'foo=bar;baz=bat';
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], iterator_to_array(new Pairs($qs, false, false, ';')));
        $qs = 'foo=bar;baz=bat';
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], iterator_to_array((new Pairs($qs))->withSeparator(';')));
    }

    public function testPairsWithMissingValues()
    {
        $qs = 'foo=&baz';
        $this->assertEquals(['foo' => '', 'baz' => null], iterator_to_array(new Pairs($qs, false, false)));
    }

    public function testPairsOnEmptyQueryString()
    {
        $qs = ' ';
        $this->assertEquals([], iterator_to_array(new Pairs($qs)));
    }
}
