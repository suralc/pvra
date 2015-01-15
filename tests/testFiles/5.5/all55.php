<?php

function im_false()
{
    return false;
}

function barabaz($args)
{
    try {
        foreach ($args as list($bar, $baz)) {
            if (!empty(im_false())) {
                yield 'My Message'[5];
            } else {
                yield [1, 2, 3, 4][0];
            }

            $bar = $baz[5];
        }
    } catch (\Exception $e) {
        print_r(\Exception::class);
    } finally {
        echo "I'm done";
    }
}

empty($a);
empty($a['abc']);
empty($a->b);
empty($a->$$b);
empty(IntlBreakIterator::DONE); // ;( http://3v4l.org/RvYB7
empty($a::$b);
