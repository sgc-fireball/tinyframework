<?php

return [
    'test1' => 'test1',
    'test2.test3' => 'test3',
    'test4' => [
        'test5' => 'test5'
    ],
    'test6' => 'test {test} test',
    'test7' => 'test {test1} test {test2} test',
    'test8' => 'test {test2} test {test1} test',
    'test9' => 'test {test:%2d} test {test:%.3f} test',
    'numbers' => '[0] zero|[1] one|[2] two|[3,*] more',
];
