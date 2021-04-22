# MySQL

- [Introduction](#introduction)
- [Select](#select)
- [Filter](#filter)
- [Sorting](#sorting)
- [Other](#other)

## Introduction

```php
/** @var \TinyFramework\Database\MySQL\Query $query */
$query = container('database')->query();
```

### Select

```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query->select(['field1', 'field2']);
```

```sql
SELECT field1, field2...
```

### From
```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query->table('tablename');
```

```sql
... FROM tablename
```


### Filter

#### AND filter

```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query
    ->where($field = 'field1', $operation = '=', $value = 'peter')
    ->where($field = 'field2', $operation = '=', $value = 'lustig');
```

```sql
... WHERE field1 = "peter" and field2 = "lustig"
```

#### OR Filter

```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query
    ->where($field = 'field1', $operation = '=', $value = 'peter')
    ->orWhere($field = 'field2', $operation = '=', $value = 'lustig');
```

```sql
... WHERE field1 = "peter" or field2 = "lustig"
```

#### Combines

```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query
    ->where($field = 'field1', $operation = '=', $value = 'peter')
    ->whereNested(function(Query $query){
        $query
            ->where($field = 'field2', $operation = '=', $value = 'lustig')
            ->orWhere($field = 'field3', $operation = '=', $value = 'mayer');
    });
```

```sql
... WHERE field1 = "peter" and (field2 = "lustig" or field3 ? "mayer")
```

### Sorting

```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query
    ->orderBy('field1', 'desc')
    ->orderBy('field2', 'asc');
```

```sql
... ORDER BY field1 desc, field2 asc
```

### Other
```php
use TinyFramework\Database\MySQL\Query;
/** @var Query $query */
$query->each(function($row) { /* ... */ });
$query->first(); // first row (LIMIT 1)
$query->count(); // integer (SELECT COUNT(1))
$query->delete(); // DELETE FROM ...
$query->firstOrFail(); // first row or \RuntimeException if empty
$query->limit(5); // LIMIT 5
$query->offset(3); // OFFSET 3
$query->paginate(20, 1); // [data => [], paging => [...]]
```
