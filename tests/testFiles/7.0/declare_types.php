<?php
declare(ticks=1);
declare(ticks=12) {
    echo 'Foo';
    declare(encoding='utf8') {}
}
declare(strict_types=0);
declare(strict_types=1);
declare(strict_types=1, ticks=12);
declare(ticks=12,strict_types=0);