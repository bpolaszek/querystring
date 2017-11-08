<?php

namespace BenTools\QueryString\Tests;

use BenTools\QueryString\Renderer\ArrayValuesNormalizerRenderer;
use BenTools\QueryString\Renderer\NativeRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use function BenTools\QueryString\query_string;
use BenTools\QueryString\QueryString;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;

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
            public function render(QueryString $queryString): string
            {
            }

            public function getEncoding(): int
            {
                // TODO: Implement getEncoding() method.
            }

            public function withEncoding(int $encoding): QueryStringRendererInterface
            {
                // TODO: Implement withEncoding() method.
            }

            public function getSeparator(): string
            {
                // TODO: Implement getSeparator() method.
            }

            public function withSeparator(?string $separator): QueryStringRendererInterface
            {
                // TODO: Implement withSeparator() method.
            }
        });
        $this->assertNotSame($qs, $qs2);
    }
}
