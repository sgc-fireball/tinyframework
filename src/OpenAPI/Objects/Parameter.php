<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;
use TinyFramework\OpenAPI\ParameterIn;
use TinyFramework\OpenAPI\Types\AbstractType;

/**
 * @link https://swagger.io/specification/#parameter-object
 */
class Parameter extends AbstractObject
{

    public ?string $name;
    public ParameterIn $in;

    public ?string $style = null;
    public ?string $description = null;
    public bool $required = false;
    public bool $deprecated = false;
    public bool $allowEmptyValue = false;
    public bool $explode = false;
    public mixed $example = null;

    public AbstractType|Reference|null $schema = null;

    /** @var null|stdClass<Example|Reference> */
    public ?object $examples = null;

    /** @var null|stdClass<MediaType> */
    public ?object $content = null;

    public static function parse(array $arr): Parameter
    {
        if (!array_key_exists('in', $arr) || !ParameterIn::tryFrom($arr['in'])) {
            throw new \InvalidArgumentException('Parameter::in must be defined and one of: query, header, path, cookie');
        }
        $object = new Parameter();
        $object->in = ParameterIn::from($arr['in']);
        if (!array_key_exists('name', $arr) || !$arr['name']) {
            throw new \InvalidArgumentException('Parameter::name is missing.');
        }
        $object->name = $arr['name'];
        if (array_key_exists('style', $arr)) {
            $object->style = $arr['style'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('required', $arr)) {
            $object->required = (bool)$arr['required'];
        }
        if (array_key_exists('deprecated', $arr)) {
            $object->deprecated = (bool)$arr['deprecated'];
        }
        if (array_key_exists('allowEmptyValue', $arr)) {
            $object->allowEmptyValue = (bool)$arr['allowEmptyValue'];
        }
        if (array_key_exists('explode', $arr)) {
            $object->explode = (bool)$arr['explode'];
        }
        if (array_key_exists('schema', $arr)) {
            $object->schema = AbstractType::parse($arr['schema']);
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
        if (array_key_exists('content', $arr)) {
            $object->content = new stdClass();
            foreach ($arr['content'] as $key => $content) {
                $object->content->{$key} = MediaType::parse($content);
            }
        }

        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

}
