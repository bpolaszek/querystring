<?php

namespace BenTools\QueryString\Parser;

class NativeParser implements QueryStringParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(string $queryString): array
    {
        $params = [];
        \parse_str(\ltrim($queryString, '?'), $params);
        return $params;
    }
}
