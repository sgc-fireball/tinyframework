# Validation

- [Introduction](#introduction)
- [Rules](#rules)
  - [Accepted](#accepted)
  - [Array](#array)
  - [Between](#between)
  - [Boolean](#boolean)
  - [Confirmed](#confirmed)
  - [Email](#email)
  - [File](#file)
  - [Filled](#filled)
  - [Float](#float)
  - [Image](#image)
  - [In](#in)
  - [Integer](#integer)
  - [Ip](#ip)
  - [Ipv4](#ipv4)
  - [Ipv6](#ipv6)
  - [Json](#json)
  - [Max](#max)
  - [Mimetypes](#mimetypes)
  - [Min](#min)
  - [NotIn](#notin)
  - [Nullable](#nullable)
  - [Numeric](#numeric)
  - [Password](#password)
  - [Present](#present)
  - [Prohibited](#prohibited)
  - [Required](#required)
  - [Sometimes](#sometimes)
  - [String](#string)
  - [Timezone](#timezone)
  - [Url](#url)
  - [Video](#video)

## Introduction

```php
validator()->validate(
    ['email' => 'exmaple@gmail.com'], // or $request,
    ['email' => 'required|string|email']
);
```

## Rules

### Accepted

### Array

### Between

```php
validator()->validate(
    ['value' => '5'],
    ['value' => 'between:0,10']
);
```

### Boolean

### Confirmed

### Email

### File

### Filled

### Float

### Image

### In

```php
validator()->validate(
    ['value' => '2'],
    ['value' => 'in:1,2,3']
);
```

### Integer

### Ip

### Ipv4

### Ipv6

### Json

### Max

```php
validator()->validate(
    ['value' => '5'],
    ['value' => 'max:10']
);
```

### Mimetypes

### Min

```php
validator()->validate(
    ['value' => '5'],
    ['value' => 'min:0']
);
```

### NotIn

### Nullable

### Numeric

### Password

### Present

### Prohibited

### Required

### Sometimes

### String

### Timezone

### Url

### Video
