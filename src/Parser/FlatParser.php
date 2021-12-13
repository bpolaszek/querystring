<?php

namespace BenTools\QueryString\Parser;

use function explode;
use function is_array;
use function urldecode;

final class FlatParser implements QueryStringParserInterface
{
    /**
     * @var string
     */
    private $separator;

    public function __construct(string $separator = '&')
    {
        $this->separator = $separator;
    }

    /**
     * @return array<string, mixed>
     */
    public function parse(string $queryString): array
    {
        $params = [];
        $pairs = explode($this->separator, $queryString);
        foreach ($pairs as $pair) {
            if (!isset($params[$pair]) && false === strpos($pair, '=')) {
                $key = urldecode($pair);
                $params[$key] = null;
                continue;
            }
            [$key, $value] = explode('=', $pair);
            $key = urldecode($key);
            $value = urldecode($value);
            if (!isset($params[$key])) {
                $params[$key] = $value;
            } elseif (!is_array($params[$key])) {
                $params[$key] = [$params[$key], $value];
            } else {
                $params[$key][] = $value;
            }
        }

        return $params;
    }
}
