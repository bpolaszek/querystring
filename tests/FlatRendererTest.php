<?php

namespace BenTools\QueryString\Tests;

use PHPUnit\Framework\TestCase;
use function BenTools\QueryString\flat;
use function BenTools\QueryString\query_string;

class FlatRendererTest extends TestCase
{
    public function testRenderer()
    {
        $data = [
            'foo' => 'bar',
            'foos' => [
                'bar',
                'foo bar',
            ],
            'fruits' => [
                'banana' => 'yellow',
                'strawberry' => 'red',
            ],
        ];

        $qs = query_string($data);
        $renderer = flat();

        $this->assertEquals('foo=bar&foos=bar&foos=foo%20bar&fruits=yellow&fruits=red', (string) $qs->withRenderer(
            $renderer
        ));

        $this->assertEquals('foo=bar&foos=bar&foos=foo+bar&fruits=yellow&fruits=red', (string) $qs->withRenderer(
            $renderer->withEncoding(PHP_QUERY_RFC1738)
        ));

        $this->assertEquals('foo=bar;foos=bar;foos=foo+bar;fruits=yellow;fruits=red', (string) $qs->withRenderer(
            $renderer->withEncoding(PHP_QUERY_RFC1738)->withSeparator(';')
        ));
    }

}
