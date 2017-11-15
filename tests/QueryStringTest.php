<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Parser\QueryStringParserInterface;
use BenTools\QueryString\QueryString;
use BenTools\QueryString\Renderer\ArrayValuesNormalizerRenderer;
use BenTools\QueryString\Renderer\NativeRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use BenTools\QueryString\Renderer\QueryStringRendererTrait;
use IteratorIterator;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use function BenTools\QueryString\query_string;
use function BenTools\QueryString\withoutNumericIndices;

class QueryStringTest extends TestCase
{

    public function testFactory()
    {
        $array = ['foo' => 'bar', 'baz' => 'bat'];
        $qs = query_string($array);
        $this->assertInstanceOf(QueryString::class, $qs);
        $this->assertEquals('foo=bar&baz=bat', (string) $qs);
        $this->assertEquals($array, $qs->getParams());


        $uri = Http::createFromString('http://www.example.net?foo=bar&baz=bat');
        $qs = query_string($uri);
        $this->assertInstanceOf(QueryString::class, $qs);
        $this->assertEquals('foo=bar&baz=bat', (string) $qs);


        $qs = query_string('foo=bar&baz=bat');
        $this->assertInstanceOf(QueryString::class, $qs);
        $this->assertEquals('foo=bar&baz=bat', (string) $qs);

        $qs = query_string();
        $this->assertInstanceOf(QueryString::class, $qs);
        $this->assertEquals('', (string) $qs);
        $this->assertEquals([], $qs->getParams());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryFailsWithInvalidArgument()
    {
        query_string(new \stdClass());
    }


    public function testCurrentLocationFactory()
    {
        $_SERVER['REQUEST_URI'] = 'foo=bar&baz=bat';

        $qs = QueryString::createFromCurrentLocation();
        $this->assertEquals('foo=bar&baz=bat', (string) $qs);

        $qs = query_string('bat=bab')->withCurrentLocation();
        $this->assertEquals('foo=bar&baz=bat', (string) $qs);

        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * In CLI mode, $_SERVER['REQUEST_URI'] should not be set.
     * @expectedException  \RuntimeException
     */
    public function testCurrentLocationFactoryFailsWhenNotSet()
    {
        QueryString::createFromCurrentLocation();
    }

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
        $renderer = NativeRenderer::factory();
        $this->assertEquals((string) $qs, $renderer->render($qs));
        $renderer = NativeRenderer::factory(PHP_QUERY_RFC3986);
        $this->assertEquals((string) $qs, $renderer->render($qs));
    }

    public function testChangeRenderer()
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
        $renderer = ArrayValuesNormalizerRenderer::factory();
        $qs = $qs->withRenderer($renderer);
        $this->assertEquals((string) $qs, $renderer->render($qs));
    }

    public function testDefaultRenderer()
    {
        $renderer = QueryString::getDefaultRenderer();
        $this->assertEquals(NativeRenderer::class, get_class(QueryString::getDefaultRenderer()));
        $this->assertSame($renderer, query_string()->getRenderer());

        $renderer = ArrayValuesNormalizerRenderer::factory();
        QueryString::setDefaultRenderer($renderer);
        $this->assertEquals(ArrayValuesNormalizerRenderer::class, get_class(QueryString::getDefaultRenderer()));
        $this->assertSame($renderer, query_string()->getRenderer());

        QueryString::restoreDefaultRenderer();
        $this->assertEquals(NativeRenderer::class, get_class(QueryString::getDefaultRenderer()));
        $this->assertSame(QueryString::getDefaultRenderer(), query_string()->getRenderer());
    }

    public function testAddParam()
    {
        $qs = query_string(['foo' => 'bar']);
        $qs = $qs->withParam('bar', 'baz');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $qs->getParams());
    }

    public function testReplaceParams()
    {
        $qs = query_string(['foo' => 'bar']);
        $qs = $qs->withParams(['bar' => 'baz']);
        $this->assertEquals(['bar' => 'baz'], $qs->getParams());
    }

    public function testSimpleGetParam()
    {
        $qs = query_string(['foo' => 'bar']);
        $this->assertTrue($qs->hasParam('foo'));
        $this->assertEquals('bar', $qs->getParam('foo'));
        $this->assertFalse($qs->hasParam('bar'));
        $this->assertNull($qs->getParam('bar'));
    }

    public function getComplexGetParam()
    {
        $data = [
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
            ],
        ];
        $qs = query_string($data);

        // 1st level
        $this->assertEquals($data['filters'], $qs->getParam('filters'));
        $this->assertNull($qs->getParam('filters', 'bar'));

        // 2nd level
        $this->assertTrue($qs->hasParam('filters', 'foo'));
        $this->assertEquals($data['filters']['foo'], $qs->getParam('filters', 'foo'));
        $this->assertFalse($qs->hasParam('filters', 'bar'));
        $this->assertNull($qs->getParam('filters', 'bar'));

        // 3rd level
        $this->assertTrue($qs->hasParam('filters', 'foo', 0));
        $this->assertEquals($data['filters']['foo'][0], $qs->getParam('filters', 'foo', 0));
        $this->assertTrue($qs->hasParam('filters', 'foo', 1));
        $this->assertEquals($data['filters']['foo'][1], $qs->getParam('filters', 'foo', 1));
        $this->assertFalse($qs->hasParam('filters', 'foo', 2));
        $this->assertNull($qs->getParam('filters', 'foo', 2));
        $this->assertFalse($qs->hasParam('filters', 'bar', 0));
        $this->assertNull($qs->getParam('filters', 'bar', 0));
    }

    public function testSimpleWithoutParam()
    {
        $qs = query_string(['foo' => 'bar', 'bar' => 'baz']);
        $qs = $qs->withoutParam('bar');
        $this->assertEquals(['foo' => 'bar'], $qs->getParams());
    }

    public function testComplexWithoutParam()
    {
        $data = [
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
                'bar' => [
                    'bar' => 'foo',
                    'foo' => 'bar',
                ],
            ],
        ];

        // Try to remove unexisting params
        $qs = query_string($data);
        $qs = $qs->withoutParam('filters', 'dummy');
        $this->assertEquals($data, $qs->getParams());
        $qs = $qs->withoutParam('filters', 'dummy', 'dummy');
        $this->assertEquals($data, $qs->getParams());
        $qs = $qs->withoutParam('filters', 'foo', 'dummy');
        $this->assertEquals($data, $qs->getParams());

        // Try to remove 2nd level key
        $qs2 = $qs->withoutParam('filters', 'bar');
        $this->assertEquals([
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
            ]
        ], $qs2->getParams());

        // Try to remove 3rd level key
        $qs2 = $qs->withoutParam('filters', 'foo', 0);
        $this->assertEquals([
            'filters' => [
                'foo' => [
                    'baz',
                ],
                'bar' => [
                    'bar' => 'foo',
                    'foo' => 'bar',
                ],
            ]
        ], $qs2->getParams());

        $qs2 = $qs->withoutParam('filters', 'foo', 1);
        $this->assertEquals([
            'filters' => [
                'foo' => [
                    'bar',
                ],
                'bar' => [
                    'bar' => 'foo',
                    'foo' => 'bar',
                ],
            ]
        ], $qs2->getParams());

        $qs2 = $qs->withoutParam('filters', 'bar', 'bar');
        $this->assertEquals([
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
                'bar' => [
                    'foo' => 'bar',
                ],
            ]
        ], $qs2->getParams());

        $qs2 = $qs->withoutParam('filters', 'bar', 'foo');
        $this->assertEquals([
            'filters' => [
                'foo' => [
                    'bar',
                    'baz',
                ],
                'bar' => [
                    'bar' => 'foo',
                ],
            ]
        ], $qs2->getParams());
    }

    public function testGetPairs()
    {
        $qs = query_string('a=b&c=d&e[]=f&e[]=g&h[foo]=bar&h[bar][]=baz&h[bar][]=bat&boo')->withRenderer(withoutNumericIndices());
        $pairs = new IteratorIterator($qs->getPairs());
        $pairs->rewind();

        $this->assertEquals('a', $pairs->key());
        $this->assertEquals('b', $pairs->current());

        $pairs->next();

        $this->assertEquals('c', $pairs->key());
        $this->assertEquals('d', $pairs->current());

        $pairs->next();

        $this->assertEquals('e%5B%5D', $pairs->key());
        $this->assertEquals('f', $pairs->current());

        $pairs->next();

        $this->assertEquals('e%5B%5D', $pairs->key());
        $this->assertEquals('g', $pairs->current());

        $pairs->next();

        $this->assertEquals('h%5Bfoo%5D', $pairs->key());
        $this->assertEquals('bar', $pairs->current());

        $pairs->next();

        $this->assertEquals('h%5Bbar%5D%5B%5D', $pairs->key());
        $this->assertEquals('baz', $pairs->current());

        $pairs->next();

        $this->assertEquals('h%5Bbar%5D%5B%5D', $pairs->key());
        $this->assertEquals('bat', $pairs->current());

        $pairs->next();

        $this->assertEquals('boo', $pairs->key());
        $this->assertEquals('', $pairs->current());


        $pairs->next();
        $this->assertNull($pairs->current());
    }

    public function testPairsWithKeyDecoding()
    {
        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator($qs->getPairs(true));
        $pairs->rewind();
        $this->assertEquals('foo[bar]', $pairs->key());
        $this->assertEquals('baz%20bat', $pairs->current());
    }

    public function testPairsWithValueDecoding()
    {
        $qs = query_string('foo[bar]=baz bat');
        $pairs = new IteratorIterator($qs->getPairs(false, true));
        $pairs->rewind();
        $this->assertEquals('foo%5Bbar%5D', $pairs->key());
        $this->assertEquals('baz bat', $pairs->current());
    }

    public function testChangeEncoding()
    {
        $qs = query_string(['foo' => 'foo bar']);
        $this->assertEquals('foo=foo%20bar', (string) $qs);
        $qs = $qs->withRenderer(
            $qs->getRenderer()->withEncoding(PHP_QUERY_RFC1738)
        );
        $this->assertEquals('foo=foo+bar', (string) $qs);
        $qs = $qs->withRenderer(
            $qs->getRenderer()->withEncoding(PHP_QUERY_RFC3986)
        );
        $this->assertEquals('foo=foo%20bar', (string) $qs);
    }

    public function testImmutability()
    {
        $qs = query_string([]);
        $qs2 = $qs->withParam('bar', 'baz');
        $this->assertNotSame($qs, $qs2);

        $qs = query_string(['foo' => 'bar']);
        $qs2 = $qs->withoutParam('dummy');
        $this->assertNotSame($qs, $qs2);

        $qs = query_string(['foo' => 'bar']);
        $qs2 = $qs->withParams(['bar' => 'baz']);
        $this->assertNotSame($qs, $qs2);

        $qs = query_string([]);
        $qs2 = $qs->withRenderer(new class implements QueryStringRendererInterface {

            use QueryStringRendererTrait;

            public function render(QueryString $queryString): string
            {
                return '';
            }

        });
        $this->assertNotSame($qs, $qs2);
    }

    public function testAnotherParser()
    {
        $dummyParser = new class implements QueryStringParserInterface
        {
            public function parse(string $queryString): array
            {
                return ['ho' => 'hi'];
            }

        };
        $qs = query_string('foo=bar', $dummyParser);
        $this->assertEquals(['ho' => 'hi'], $qs->getParams());
    }

    public function testChangeDefaultParser()
    {
        $dummyParser = new class implements QueryStringParserInterface
        {
            public function parse(string $queryString): array
            {
                return ['ho' => 'hi'];
            }

        };
        QueryString::setDefaultParser($dummyParser);
        $qs = query_string('foo=bar');
        $this->assertEquals(['ho' => 'hi'], $qs->getParams());

        QueryString::restoreDefaultParser();
        $qs = query_string('foo=bar');
        $this->assertEquals(['foo' => 'bar'], $qs->getParams());
    }
}
