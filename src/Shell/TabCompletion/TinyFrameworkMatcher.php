<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

use Psy\TabCompletion\Matcher\AbstractMatcher;

class TinyFrameworkMatcher extends AbstractMatcher
{

    public function getMatches(array $tokens, array $info = [])
    {
        // @TODO
        return [];
    }

    public function hasMatched(array $tokens)
    {
        return false;
    }

}
