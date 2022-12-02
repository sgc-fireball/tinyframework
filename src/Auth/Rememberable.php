<?php

declare(strict_types=1);

namespace TinyFramework\Auth;

interface Rememberable
{

    public function rememberToken(string|null $token): Rememberable|string|null;

}
