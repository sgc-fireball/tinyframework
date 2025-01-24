# Logger

- [Introduction](#introduction)
- [Driver](#driver)
  - [File](#file)
  - [DIO](#dio)

## Introduction

```php
logger()->alert($message, $context);
logger()->critical($message, $context);
logger()->debug($message, $context);
logger()->emergency($message, $context);
logger()->error($message, $context);
logger()->info($message, $context);
logger()->notice($message, $context);
logger()->warning($message, $context);
logger()->log($level, $message, $context);
```

## Driver

### File

```php
logger('file')->info($message)
```

### DIO

```php
logger('dio')->info($message)
```
