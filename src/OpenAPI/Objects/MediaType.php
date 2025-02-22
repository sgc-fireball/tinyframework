<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;
use TinyFramework\OpenAPI\Types\AbstractType;

/**
 * @link https://swagger.io/specification/#media-type-object
 */
class MediaType extends AbstractObject
{

    public AbstractType|Reference|null $schema = null;
    public mixed $example = null;
    /** @var null|stdClass<Example|Reference> */
    public ?object $examples = null;
    /** @var null|stdClass<Encoding> */
    public ?object $encoding = null;

    public static function parse(array $arr): MediaType
    {
        $object = new MediaType();
        if (array_key_exists('schema', $arr)) {
            if (array_key_exists('$ref', $arr['schema'])) {
                $object->schema = Reference::parse($arr['schema']);
            } else {
                $object->schema = AbstractType::parse($arr['schema']);
            }
        }
        if (array_key_exists('example', $arr)) {
            $object->example = $arr['example'];
        }
        if (array_key_exists('examples', $arr)) {
            $object->examples = new stdClass();
            foreach ($arr['examples'] as $key => $example) {
                if (array_key_exists('$ref', $example)) {
                    $object->examples->{$key} = Reference::parse($example);
                } else {
                    $object->examples->{$key} = Example::parse($example);
                }
            }
        }
        if (array_key_exists('encoding', $arr)) {
            $object->encoding = new stdClass();
            foreach ($arr['encoding'] as $key => $example) {
                $object->encoding->{$key} = Encoding::parse($example);
            }
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

}
