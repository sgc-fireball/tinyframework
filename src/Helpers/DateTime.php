<?php

namespace TinyFramework\Helpers;

use DateTimeZone;

class DateTime extends \DateTime
{

    public function __construct(
        string $datetime = 'now',
        DateTimeZone|null $timezone = null
    ) {
        parent::__construct($datetime, $timezone ?? new DateTimeZone(config('app.timezone') ?: 'UTC'));
    }

    // @TODO implement cool fancy features like carbon.

}
