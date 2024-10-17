<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\AbstractObject;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class NullType extends AbstractType
{

    public string $type = 'null';
    public bool $nullable = true;
    public ?string $description = null;
    public null $default = null;
    public null $example = null;
    public ?XMLSettings $xml = null;

    public static function parse(array $arr): NullType
    {
        $object = new NullType();
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }

    public function validate(mixed $value): void
    {
        if ($value !== null) {
            throw new OpenAPIException('Invalid null value.', 400);
        }
    }
}
