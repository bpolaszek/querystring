<?php

namespace BenTools\QueryString;

use BenTools\QueryString\Parser\NativeParser;
use BenTools\QueryString\Parser\QueryStringParserInterface;
use BenTools\QueryString\Renderer\NativeRenderer;
use BenTools\QueryString\Renderer\QueryStringRendererInterface;
use Psr\Http\Message\UriInterface;

class QueryStringFactory
{
    /**
     * @var QueryStringParserInterface
     */
    private $parser;

    /**
     * @var QueryStringRendererInterface
     */
    private $renderer;

    /**
     * QueryStringFactory constructor.
     */
    public function __construct(?QueryStringParserInterface $parser = null, ?QueryStringRendererInterface $renderer = null)
    {
        $this->parser = $parser ?? new NativeParser();
        $this->renderer = $renderer ?? new NativeRenderer();
    }

    /**
     * @param array $params
     * @return QueryString
     */
    private function createFromParams(array $params): QueryString
    {
        return new QueryString($params, $this->renderer);
    }

    /**
     * @param string $string
     * @return QueryString
     */
    private function createFromString(string $string): QueryString
    {
        return $this->createFromParams($this->parser->parse(\ltrim($string, '?')));
    }

    /**
     * @param $uri
     * @return QueryString
     */
    private function createFromUri($uri): QueryString
    {
        if (!$uri instanceof UriInterface) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of %s, got %s', UriInterface::class, \is_object($uri) ? \get_class($uri) : \gettype($uri)));
        }

        return $this->createFromString($uri->getQuery());
    }

    /**
     * @return QueryString
     */
    public function fromCurrentLocation(): QueryString
    {
        if (!isset($_SERVER['QUERY_STRING'])) {
            throw new \RuntimeException('Query string has not been defined by the SAPI used.');
        }

        return $this->createFromString($_SERVER['QUERY_STRING']);
    }

    /**
     * @param $input
     * @return QueryString
     */
    public function create($input): QueryString
    {
        if (\is_array($input) || null === $input) {
            return $this->createFromParams($input ?? []);
        }

        if ($input instanceof UriInterface) {
            return $this->createFromUri($input);
        }

        if (\is_string($input)) {
            return $this->createFromString($input);
        }

        throw new \InvalidArgumentException(sprintf('Expected array, string or Psr\Http\Message\UriInterface, got %s', \is_object($input) ? \get_class($input) : \gettype($input)));
    }
}