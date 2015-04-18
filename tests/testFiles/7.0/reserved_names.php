<?php

namespace {
    class_alias('Foo', 'string');
    class_alias('Foo', 'bool');
    class_alias('Foo', 'int');
    class_alias('Foo', 'float');

    class_alias('Foo', 'strIng');
    class_alias('Foo', 'BooL');
    class_alias('Foo', 'iNt');
    class_alias('Foo', 'flOaT');

    class_alias('Foo', '\Foo\Bar\String');
    class_alias('Foo', '\Foo\Bar\Bool');
    class_alias('Foo', '\Foo\Bar\Int');
    class_alias('Foo', '\Foo\Bar\Float');

    class_alias('Foo', '\Foo\Bar\String\SomeThingElse');
    class_alias('Foo', '\Foo\Bar\Bool\SomeThingElse');
    class_alias('Foo', '\Foo\Bar\Int\SomeThingElse');
    class_alias('Foo', '\Foo\Bar\Float\SomeThingElse');

    if(PHP_VERSION_ID > 700000) {
        class_alias('Foo', '\Foo\Bar\Float'); // detection of conditional alias and classes not implemented
    }

    class String
    {
    }

    class Bool
    {
    }

    class Float
    {
    }

    class Int
    {
    }
}

namespace Foo {
    trait String
    {
    }

    trait Bool
    {
    }

    trait Float
    {
    }

    trait Int
    {
    }
}

namespace Foo2 {
    interface String
    {
    }

    interface Bool
    {
    }

    interface Float
    {
    }

    interface Int
    {
    }
}

namespace Foo3\String {
    class Bool
    {
    }

    class BazBar
    {
    }
}
