<?php


namespace BenTools\QueryString\Merger;


use BenTools\QueryString\QueryString;

class DefaultMerger implements QueryStringMergerInterface
{
    /**
     * @inheritDoc
     */
    public function merge(QueryString $queryString, array $params, bool $replace): QueryString
    {
        return $queryString->reset(
            \array_replace(
                $queryString->getParams(),
                $params
            )
        );
    }
}