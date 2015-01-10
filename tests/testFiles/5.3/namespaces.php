<?php
namespace {
    echo 'Hello world from ', __NAMESPACE__;
}

namespace abc {
    $gear = __NAMESPACE__;
    echo $gear;
}

namespace delta {
    use abc;
}
