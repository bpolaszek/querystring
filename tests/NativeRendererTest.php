<?php

namespace BenTools\QueryString\Tests;

use function BenTools\QueryString\native;
use BenTools\QueryString\Renderer\NativeRenderer;
use function BenTools\QueryString\query_string;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use PHPUnit\Framework\TestCase;

class NativeRendererTest extends TestCase
{
    private $defaultSeparator;

    public function testRenderer()
    {
        $data = [
            'foo' => 'bar',
            'sort' => [
                'bar' => 'desc',
                'foo' => 'asc',
            ],
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
                'bar' => [
                    'foo bar',
                ],
            ],
        ];

        $qs = query_string($data);
        $renderer = native();
        $this->assertInstanceOf(NativeRenderer::class, $renderer);
        $this->assertEquals('foo=bar&sort%5Bbar%5D=desc&sort%5Bfoo%5D=asc&filters%5Bfoo%5D%5B0%5D=bar&filters%5Bfoo%5D%5B1%5D=baz&filters%5Bbar%5D%5B0%5D=foo%20bar', $renderer->render($qs));
        $this->assertEquals('foo=bar&sort[bar]=desc&sort[foo]=asc&filters[foo][0]=bar&filters[foo][1]=baz&filters[bar][0]=foo bar', urldecode($renderer->render($qs)));
        $qs = $qs->withRenderer($qs->getRenderer()->withEncoding(PHP_QUERY_RFC1738));
        $this->assertEquals('foo=bar&sort%5Bbar%5D=desc&sort%5Bfoo%5D=asc&filters%5Bfoo%5D%5B0%5D=bar&filters%5Bfoo%5D%5B1%5D=baz&filters%5Bbar%5D%5B0%5D=foo+bar', $renderer->withEncoding(PHP_QUERY_RFC1738)->render($qs));
        $this->assertEquals('foo=bar&sort[bar]=desc&sort[foo]=asc&filters[foo][0]=bar&filters[foo][1]=baz&filters[bar][0]=foo bar', urldecode($qs->withRenderer($renderer->withEncoding(PHP_QUERY_RFC1738))));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFails()
    {
        NativeRenderer::factory(1000);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testChangeEncodingFails()
    {
        $renderer = NativeRenderer::factory();
        $renderer->withEncoding(1000);
    }

    public function testChangeEncoding()
    {
        $renderer = NativeRenderer::factory();
        $this->assertNotSame($renderer->withEncoding($renderer->getEncoding()), $renderer);

        $this->assertEquals(QueryStringRendererInterface::DEFAULT_ENCODING, $renderer->getEncoding());
        $renderer = $renderer->withEncoding(PHP_QUERY_RFC1738);
        $this->assertEquals(PHP_QUERY_RFC1738, $renderer->getEncoding());
    }

    public function testChangeSeparator()
    {

        ini_set('arg_separator.output', '~');

        $qs = query_string(['foo' => 'bar', 'bar' => 'baz']);
        $renderer = NativeRenderer::factory();
        $this->assertNull($renderer->getSeparator());
        $this->assertEquals('foo=bar~bar=baz', $renderer->render($qs));
        $this->assertNotSame($renderer->withSeparator($renderer->getSeparator()), $renderer);

        $renderer = $renderer->withSeparator('|');
        $this->assertEquals('|', $renderer->getSeparator());
        $this->assertEquals('foo=bar|bar=baz', $renderer->render($qs));

        $renderer = $renderer->withSeparator(null); // Reset to default
        $this->assertEquals('foo=bar~bar=baz', $renderer->render($qs));

        $renderer = $renderer->withSeparator(''); // Blank separator
        $this->assertEquals('foo=barbar=baz', $renderer->render($qs));

        ini_set('arg_separator.output', $this->defaultSeparator);
    }

    public function setUp()
    {
        $this->defaultSeparator = ini_get('arg_separator.output');
    }

    public function tearDown()
    {
        ini_set('arg_separator.output', $this->defaultSeparator);
    }
}
