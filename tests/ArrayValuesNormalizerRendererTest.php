<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Renderer\ArrayValuesNormalizerRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use PHPUnit\Framework\TestCase;
use function BenTools\QueryString\query_string;
use function BenTools\QueryString\withoutNumericIndices;

class ArrayValuesNormalizerRendererTest extends TestCase
{

    private $defaultSeparator;

    public function testRenderer(): void
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
        $renderer = withoutNumericIndices();
        $this->assertInstanceOf(ArrayValuesNormalizerRenderer::class, $renderer);
        $this->assertEquals('foo=bar&sort%5Bbar%5D=desc&sort%5Bfoo%5D=asc&filters%5Bfoo%5D%5B%5D=bar&filters%5Bfoo%5D%5B%5D=baz&filters%5Bbar%5D%5B%5D=foo%20bar', $renderer->render($qs));
        $this->assertEquals('foo=bar&sort[bar]=desc&sort[foo]=asc&filters[foo][]=bar&filters[foo][]=baz&filters[bar][]=foo bar', urldecode($qs->withRenderer($renderer)));
        $this->assertEquals('foo=bar&sort%5Bbar%5D=desc&sort%5Bfoo%5D=asc&filters%5Bfoo%5D%5B%5D=bar&filters%5Bfoo%5D%5B%5D=baz&filters%5Bbar%5D%5B%5D=foo+bar', $renderer->withEncoding(PHP_QUERY_RFC1738)->render($qs));
        $this->assertEquals('foo=bar&sort[bar]=desc&sort[foo]=asc&filters[foo][]=bar&filters[foo][]=baz&filters[bar][]=foo bar', urldecode($qs->withRenderer($renderer->withEncoding(PHP_QUERY_RFC1738))));
    }

    public function testRendererOnlyAffectsKeys(): void
    {
        $data = [
            'foo' => [
                'bar baz[0]'
            ]
        ];
        $qs = query_string($data);
        $this->assertEquals('foo%5B%5D=bar%20baz%5B0%5D', (string) $qs->withRenderer(withoutNumericIndices()));
    }

    public function testChangeEncoding(): void
    {
        $renderer = ArrayValuesNormalizerRenderer::factory();
        $this->assertNotSame($renderer->withEncoding($renderer->getEncoding()), $renderer);

        $this->assertEquals(QueryStringRendererInterface::DEFAULT_ENCODING, $renderer->getEncoding());
        $renderer = $renderer->withEncoding(PHP_QUERY_RFC1738);
        $this->assertEquals(PHP_QUERY_RFC1738, $renderer->getEncoding());
    }

    public function testChangeSeparator(): void
    {

        ini_set('arg_separator.output', '~');

        $qs = query_string(['foo' => 'bar', 'bar' => 'baz']);
        $renderer = ArrayValuesNormalizerRenderer::factory();
        $this->assertNull($renderer->getSeparator());
        $this->assertEquals('foo=bar~bar=baz', $renderer->render($qs));
        $this->assertNotSame($renderer->withSeparator($renderer->getSeparator()), $renderer);

        $renderer = $renderer->withSeparator('|');
        $this->assertEquals('|', $renderer->getSeparator());
        $this->assertEquals('foo=bar|bar=baz', $renderer->render($qs));

        $renderer = $renderer->withSeparator(null); // Reset to default
        $this->assertEquals('foo=bar~bar=baz', $renderer->render($qs));

        ini_set('arg_separator.output', $this->defaultSeparator);
    }

    public function testBlankSeparator(): void
    {
        $this->expectException(\RuntimeException::class);
        $qs = query_string(['foo' => 'bar', 'bar' => 'baz']);
        $renderer = ArrayValuesNormalizerRenderer::factory();
        $renderer = $renderer->withSeparator(''); // Blank separator
        $renderer->render($qs);
    }

    public function setUp(): void
    {
        $this->defaultSeparator = ini_get('arg_separator.output');
    }

    public function tearDown(): void
    {
        ini_set('arg_separator.output', $this->defaultSeparator);
    }
}
