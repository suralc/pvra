<?php

return [
    'additions' => [
        'class' => [
            // 5.3
            'DateInterval' => '5.3.0',
            'DatePeriod' => '5.3.0',
            'Phar' => '5.3.0',
            'PharData' => '5.3.0',
            'PharException' => '5.3.0',
            'PharFileInfo' => '5.3.0',
            'FilesystemIterator' => '5.3.0',
            'GlobIterator' => '5.3.0',
            'MultipleIterator' => '5.3.0',
            'RecursiveTreeIterator' => '5.3.0',
            'SplDoublyLinkedList' => '5.3.0',
            'SplFixedArray' => '5.3.0',
            'SplHeap' => '5.3.0',
            'SplMaxHeap' => '5.3.0',
            'SplMinHeap' => '5.3.0',
            'SplPriorityQueue' => '5.3.0',
            'SplQueue' => '5.3.0',
            'SplStack' => '5.3.0',
            // 5.4
            'CallbackFilterIterator' => '5.4.0',
            'RecursiveCallbackFilterIterator' => '5.4.0',
            'ReflectionZendExtension' => '5.4.0',
            'JsonSerializable' => '5.4.0',
            'SessionHandler' => '5.4.0',
            'SessionHandlerInterface' => '5.4.0',
            'SNMP' => '5.4.0',
            'Transliterator' => '5.4.0',
            'Spoofchecker' => '5.4.0',
            // 5.5
            'CURLFile' => '5.5.0',
            'DateTimeImmutable' => '5.5.0',
            'DateTimeInterface' => '5.5.0',
            'IntlCalendar' => '5.5.0',
            'IntlGregorianCalendar' => '5.5.0',
            'IntlTimeZone' => '5.5.0',
            'IntlBreakIterator' => '5.5.0',
            'IntlRuleBasedBreakIterator' => '5.5.0',
            'IntlCodePointBreakIterator' => '5.5.0',
            // 5.6
        ],
        'function' => [
            // 5.3
            // core
            'array_replace' => '5.3.0',
            'array_replace_recursive' => '5.3.0',
            'class_alias' => '5.3.0',
            'forward_static_call' => '5.3.0',
            'forward_static_call_array' => '5.3.0',
            'gc_collect_cycles' => '5.3.0',
            'gc_disable' => '5.3.0',
            'gc_enable' => '5.3.0',
            'gc_enabled' => '5.3.0',
            'get_called_class' => '5.3.0',
            'gethostname' => '5.3.0',
            'header_remove' => '5.3.0',
            'lcfirst' => '5.3.0',
            'parse_ini_string' => '5.3.0',
            'quoted_printable_encode' => '5.3.0',
            'str_getcsv' => '5.3.0',
            'stream_context_set_default' => '5.3.0',
            'stream_supports_lock' => '5.3.0',
            'stream_context_get_params' => '5.3.0',
            // datetime
            'date_add' => '5.3.0',
            'date_create_from_format' => '5.3.0',
            'date_diff' => '5.3.0',
            'date_get_last_errors' => '5.3.0',
            'date_parse_from_format' => '5.3.0',
            'date_sub' => '5.3.0',
            'timezone_version_get' => '5.3.0',
            // gmp
            'gmp_testbit' => '5.3.0',
            // hash
            'imap_gc' => '5.3.0',
            'imap_utf8_to_mutf7' => '5.3.0',
            'imap_mutf2_to_utf8' => '5.3.0',
            // json
            'json_last_error' => '5.3.0',
            // mysqli
            'mysqli_fetch_all' => '5.3.0',
            'mysqli_get_connection_stats' => '5.3.0',
            'mysqli_poll' => '5.3.0',
            'mysqli_reap_async_query' => '5.3.0',
            'openssl_random_pseudo_bytes' => '5.3.0',
            // pcntl
            'pcntl_signlar_dispatch' => '5.3.0',
            'pcntl_sigprocmask' => '5.3.0',
            'pcntl_sigtimedwait' => '5.3.0',
            'pcntl_sigwaitinfo' => '5.3.0',
            // pcre
            'preg_filter' => '5.3.0',
            // semaphore
            'msg_queue_exists' => '5.3.0',
            'shm_has_var' => '5.3.0',
            // 5.4
            'hex2bin' => '5.4.0',
            'http_response_code' => '5.4.0',
            'get_declared_traits' => '5.4.0',
            'getimagesizefromstring' => '5.4.0',
            'stream_set_chunk_size' => '5.4.0',
            'socket_import_stream' => '5.4.0',
            'trait_exists' => '5.4.0',
            'header_register_callback' => '5.4.0',
            'class_uses' => '5.4.0',
            'session_status' => '5.4.0',
            'session_register_shutdown' => '5.4.0',
            'mysqli_error_list' => '5.4.0',
            'mysqli_stmt_error_list' => '5.4.0',
            'libxml_set_external_entitiy_loader' => '5.4.0',
            'ldap_controll_paged_result' => '5.4.0',
            'ldap_controll_paged_result_response' => '5.4.0',
            'transliterator_create' => '5.4.0',
            'transliterator_create_from_rules' => '5.4.0',
            'transliterator_create_inverse' => '5.4.0',
            'transliterator_get_error_code' => '5.4.0',
            'transliterator_get_error_message' => '5.4.0',
            'transliterator__list_ids' => '5.4.0',
            'transliterator_transliterate' => '5.4.0',
            'zlib_decode' => '5.4.0',
            'zlib_encode' => '5.4.0',
            // 5.5
            'password_get_info' => '5.5.0',
            'password_hash' => '5.5.0',
            'password_needs_rehash' => '5.5.0',
            'password_verify' => '5.5.0',
            // 5.6
            'gmp_root' => '5.6.0',
            'gmp_rootrem' => '5.6.0',
            'hash_equals' => '5.6.0',
            'ldap_escape' => '5.6.0',
            'ldap_modify_batch' => '5.6.0',
            'mysqli_get_links_stats' => '5.6.0',
            'oci_get_implicit_resultset' => '5.6.0',
            'openssl_get_cert_locations' => '5.6.0',
            'openssl_x509_fingerprint' => '5.6.0',
            'openssl_spki_new' => '5.6.0',
            'openssl_spki_verify' => '5.6.0',
            'openssl_spki_export_challenge' => '5.6.0',
            'openssl_spki_export' => '5.6.0',
            'pg_connect_poll' => '5.6.0',
            'pg_consume_input' => '5.6.0',
            'pg_flush' => '5.6.0',
            'pg_socket' => '5.6.0',
            'session_abort' => '5.6.0',
            'session_reset' => '5.6.0',
        ],
        'constant' => [
            // 5.3
            'E_DEPRECATED' => '5.3.0',
            'E_USER_DEPRECATED' => '5.3.0',
            'INI_SCANNER_NORMAL' => '5.3.0',
            'INI_SCANNER_RAW' => '5.3.0',
            'PHP_MAXPATHLEN' => '5.3.0',
            'PHP_WINDOWS_NT_DOMAIN_CONTROLLER' => '5.3.0',
            'PHP_WINDOWS_NT_SERVER' => '5.3.0',
            'PHP_WINDOWS_NT_WORKSTATION' => '5.3.0',
            'PHP_WINDOWS_VERSION_BUILD' => '5.3.0',
            'PHP_WINDOWS_VERSION_MAJOR' => '5.3.0',
            'PHP_WINDOWS_VERSION_MINOR' => '5.3.0',
            'PHP_WINDOWS_VERSION_PLATFORM' => '5.3.0',
            'PHP_WINDOWS_VERSION_PRODUCTTYPE' => '5.3.0',
            'PHP_WINDOWS_VERSION_SP_MAJOR' => '5.3.0',
            'PHP_WINDOWS_VERSION_SP_MINOR' => '5.3.0',
            'PHP_WINDOWS_VERSION_SUITEMASK' => '5.3.0',
            'CURLOPT_PROGRESSFUNCTION' => '5.3.0',
            'IMG_FILTER_PIXELATE' => '5.3.0',
            'JSON_ERROR_CTLR_CHAR' => '5.3.0',
            'JSON_ERROR_DEPTH' => '5.3.0',
            'JSON_ERROR_NONE' => '5.3.0',
            'JSON_ERROR_STATE_MISMATCH' => '5.3.0',
            'JSON_ERROR_SYNTAX' => '5.3.0',
            'JSON_HEX_TAG' => '5.3.0',
            'JSON_HEX_AMP' => '5.3.0',
            'JSON_HEX_APOS' => '5.3.0',
            'JSON_HEX_QUOT' => '5.3.0',
            'LDAP_OPT_NETWORK_TIMEOUT' => '5.3.0',
            'LIBXML_LOADED_VERSION' => '5.3.0',
            'PREG_BAC_UTF8_OFFSET_ERROR' => '5.3.0',
            'BUS_ADRALN' => '5.3.0',
            'BUS_ADRERR' => '5.3.0',
            'BUS_OBJERR' => '5.3.0',
            'CLD_CONTIUNED' => '5.3.0',
            'CLD_DUMPED' => '5.3.0',
            'CLD_EXITED' => '5.3.0',
            'CLD_KILLED' => '5.3.0',
            'CLD_STOPPED' => '5.3.0',
            'CLD_TRAPPED' => '5.3.0',
            'FPE_FLTDIV' => '5.3.0',
            'FPE_FLTINV' => '5.3.0',
            'FPE_FLTOVF' => '5.3.0',
            'FPE_FLTRES' => '5.3.0',
            'FPE_FLTSUB' => '5.3.0',
            'FPE_FLTUND' => '5.3.0',
            'FPE_INTDIV' => '5.3.0',
            'FPE_INTOVF' => '5.3.0',
            'ILL_BADSTK' => '5.3.0',
            'ILL_COPROC' => '5.3.0',
            'ILL_ILLADR' => '5.3.0',
            'ILL_ILLOPC' => '5.3.0',
            'ILL_ILLOPN' => '5.3.0',
            'ILL_ILLTRP' => '5.3.0',
            'ILL_PRVOPC' => '5.3.0',
            'ILL_PRVREG' => '5.3.0',
            'POLL_ERR' => '5.3.0',
            'POLL_HUP' => '5.3.0',
            'POLL_IN' => '5.3.0',
            'POLL_MSG' => '5.3.0',
            'POLL_OUT' => '5.3.0',
            'POLL_PRI' => '5.3.0',
            'SEGV_ACCERR' => '5.3.0',
            'SEGV_MAPERR' => '5.3.0',
            'SI_ASYNCIO' => '5.3.0',
            'SI_KERNEL' => '5.3.0',
            'SI_MESGQ' => '5.3.0',
            'SI_NOINFO' => '5.3.0',
            'SI_QUEUE' => '5.3.0',
            'SI_SIGIO' => '5.3.0',
            'SI_TIMER' => '5.3.0',
            'SI_TKILL' => '5.3.0',
            'SI_USER' => '5.3.0',
            'SIG_BLOCK' => '5.3.0',
            'SIG_SETMASK' => '5.3.0',
            'SIG_UNBLOCK' => '5.3.0',
            'TRAP_BRKPT' => '5.3.0',
            'TRAP_TRACE' => '5.3.0',
        ]
    ],
    'removals' => [
        'function' => [
            // 5.4
            'define_syslog_variables' => '5.4.0',
            'import_request_variables' => '5.4.0',
            'session_is_registered' => '5.4.0',
            'session_register' => '5.4.0',
            'session_unregister' => '5.4.0',
            // 5.5
            'php_logo_guid' => '5.5.0',
            'php_egg_logo_guid' => '5.5.0',
            'php_real_logo_guid' => '5.5.0',
            'zend_logo_guid' => '5.5.0',
        ]
    ],
];
