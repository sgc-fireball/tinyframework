# Localization

- [Introduction](#introduction)

## Introduction
Translation file: `resources/lang/en/system.php`:
```php
<?php declare(strict_types=1);
return [
    'trans' => 'Text1',
    'trans.value' => 'Welcome :firstname!',
    'trans.choice' => '[0] Zero |[1,10] One to ten|[11, *] Eleven or more',
    'trans.choice.value' => '[0] Welcome :firstname|[1] Bye Bye :firstname',
];
```
```php
trans('system.trans'); // Text1
trans('system.trans.value', ['firstname' => 'Peter']); // Welcome Peter!
trans('system.trans.value', ['firstname' => 'Peter'], 'ru'); // system.key2
trans_choice('system.trans.choice', 0); // Zero
trans_choice('system.trans.choice', 5); // One to ten
trans_choice('system.trans.choice', 100); // Eleven or more
trans_choice('system.trans.choice', 100); // Eleven or more
trans_choice('system.trans.choice.value', 0, ['firstname' => 'Peter']); // Welcome Peter
trans_choice('system.trans.choice.value', 1, ['firstname' => 'Peter'], 'ru'); // system.trans.choice.value
```
