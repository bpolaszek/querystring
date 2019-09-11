<?php


namespace BenTools\QueryString\Merger;

use BenTools\QueryString\QueryString;

interface QueryStringMergerInterface
{

    /**
     * Merges a query string object with the given params.
     * Merging strategy depends on the implementation.
     *
     * @param QueryString $queryString
     * @param array $params
     * @param bool $replace
     * @return QueryString
     */
    public function merge(QueryString $queryString, array $params, bool $replace): QueryString;

}