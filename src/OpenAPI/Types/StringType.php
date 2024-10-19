<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\Helpers\DateTime;
use TinyFramework\Helpers\Uuid;
use TinyFramework\OpenAPI\Objects\AbstractObject;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class StringType extends AbstractType
{

    public string $type = 'string';
    public ?string $format = null;
    public bool $nullable = false;
    public ?string $description = null;
    public ?string $default = null;
    public ?string $example = null;
    public ?array $enum = null;
    public ?int $minLength = null;
    public ?int $maxLength = null;
    public ?string $pattern = null;
    public ?XMLSettings $xml = null;

    public static function parse(array $arr): StringType
    {
        $object = new StringType();
        if (array_key_exists('format', $arr)) {
            $object->format = $arr['format'];
        }
        if (array_key_exists('nullable', $arr)) {
            $object->nullable = (bool)$arr['nullable'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('default', $arr)) {
            $object->default = $arr['default'];
        }
        if (array_key_exists('example', $arr)) {
            $object->default = $arr['example'];
        }
        if (array_key_exists('minLength', $arr)) {
            $object->minLength = (int)$arr['minLength'];
        }
        if (array_key_exists('maxLength', $arr)) {
            $object->maxLength = (int)$arr['maxLength'];
        }
        if (array_key_exists('pattern', $arr)) {
            $object->pattern = $arr['pattern'];
        }
        if (array_key_exists('enum', $arr)) {
            $object->enum = arraY_map(fn(string $enum) => (string)$enum, $arr['enum']);
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }


    public function validate(mixed $value): void
    {
        if ($this->nullable && $value === null) {
            return;
        }
        if (!is_string($value)) {
            throw new OpenAPIException('Invalid string.', 400);
        }
        if (is_array($this->enum) && !in_array($value, $this->enum, true)) {
            throw new OpenAPIException('Invalid enum. Valid: ' . implode(',', $this->enum), 400);
        }
        if ($this->minLength !== null || $this->maxLength !== null) {
            $length = strlen($value);
            if ($this->minLength !== null && $length < $this->minLength) {
                throw new OpenAPIException('Invalid string (minimal length: ' . $this->minLength . ').', 400);
            }
            if ($this->maxLength !== null && $length > $this->maxLength) {
                throw new OpenAPIException('Invalid string (maximal length: ' . $this->minLength . ').', 400);
            }
        }
        if ($this->format) {
            $filterValidateName = 'FILTER_VALIDATE_' . strtoupper($this->format);
            if (defined($filterValidateName)) {
                if (!filter_var($value, constant($filterValidateName))) {
                    throw new OpenAPIException('Invalid ' . $this->format . '.', 400);
                }
            } elseif ($this->format === 'hex') {
                if (!preg_match('/^[0-9a-f]+$/i', $value)) {
                    throw new OpenAPIException('Invalid ' . $this->format . '.', 400);
                }
            } elseif ($this->format === 'jwt') {
                if (substr_count($value, '.') !== 2) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (1).', 400);
                }
                $jwt = explode('.', $value);
                $header = base64_decode($jwt[0]);
                $content = base64_decode($jwt[1]);
                if (!$header || !$content) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (2).', 400);
                }
                $header = json_decode($header, true);
                $content = json_decode($content, true);
                if (!is_array($header) || !is_array($content)) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (3).', 400);
                }
                if (!count($header) || !count($content)) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (4).', 400);
                }
            } elseif ($this->format === 'uuid') {
                if (strlen($value) !== 36) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (1).', 400);
                }
                if (!preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                    $value
                )) {
                    throw new OpenAPIException('Invalid ' . $this->format . ' (2).', 400);
                }
            } elseif ($this->format === 'date-time' && !preg_match(
                    '/^(\d{4}-\d{2}-\d{2})(T|\s)(\d{2}:\d{2}:\d{2})(\.\d+)?([+-]\d{2}:?\d{2})?$/',
                    $value
                )) {
                throw new OpenAPIException('Invalid ' . $this->format . '.', 400);
            } elseif ($this->format === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                throw new OpenAPIException('Invalid ' . $this->format . '.', 400);
            } elseif ($this->format === 'time' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                throw new OpenAPIException('Invalid ' . $this->format . '.', 400);
            } elseif ($this->format === 'password') {
                // no explicit check
            }
        }
        if ($this->pattern && !preg_match($this->pattern, $value)) {
            throw new OpenAPIException('Invalid string pattern.', 400);
        }
    }
}
