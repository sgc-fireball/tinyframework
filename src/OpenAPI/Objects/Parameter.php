<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
class Parameter extends AbstractObject
{

    public ?string $name;
    public string $in; // @TODO enum[query, header, path, cookie]

    public ?string $style = null;
    public ?string $description = null;
    public bool $required = false;
    public bool $deprecated = false;
    public bool $allowEmptyValue = false;
    public bool $explode = false;
    public mixed $example = null;

    public Schema|Reference|null $schema = null;

    /** @var ?object<string, Example|Reference> */
    public ?object $examples = null;

    /** @var ?object<string, MediaType> */
    public ?object $content = null;

    public static function parse(array $arr): Parameter
    {
        if (!in_array($arr['in'], ['query', 'header', 'path', 'cookie'])) {
            throw new \InvalidArgumentException('Parameter::in must be one of query, header, path, cookie');
        }
        $object = new Parameter();
        $object->in = $arr['in'];
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
            $object->schema = Schema::parse($arr['schema']);
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

        return $object->parseExtension($arr);
    }

}
