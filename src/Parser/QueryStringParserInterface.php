<?php

namespace BenTools\QueryString\Parser;

interface QueryStringParserInterface
{
    /**
     * Convert a query string into an array of parameters.
     *
     * @param string $queryString
     * @return array
     */
    public function parse(string $queryString): array;
}
