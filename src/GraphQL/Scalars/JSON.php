<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use Safe\Exceptions\JsonException;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils as GraphQLUtils;

class JSON extends ScalarType
{
    /**
     * The description that is used for schema introspection.
     *
     * @var string
     */
    public $description = 'Arbitrary data encoded in JavaScript Object Notation. See https://www.json.org/.';

    /**
     * Serializes an internal value to include in a response.
     */
    public function serialize($value): string
    {
        return json_encode($value);
    }

    /**
     * Parses an externally provided value (query variable) to use as an input.
     *
     * In the case of an invalid value this method must throw an Exception
     *
     * @throws Error
     */
    public function parseValue($value)
    {
        if (is_string($value)) {
            return $this->decodeJSON($value);
        }
        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     *
     * In the case of an invalid node or value this method must throw an Exception
     *
     * @param Node         $valueNode
     * @param mixed[]|null $variables
     *
     * @return string
     *
     * @throws Exception
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!property_exists($valueNode, 'value')) {
            throw new Error(
                'Can only parse literals that contain a value, got ' . GraphQLUtils::printSafeJson($valueNode)
            );
        }

        return $this->decodeJSON($valueNode->value);
    }

    /**
     * Try to decode a user-given value into JSON.
     *
     * @return string
     *
     * @throws Error
     */
    protected function decodeJSON($value)
    {
        try {
            $parsed = json_decode($value);
        } catch (JsonException $jsonException) {
            throw new Error(
                $jsonException->getMessage()
            );
        }

        return $parsed;
    }
}
