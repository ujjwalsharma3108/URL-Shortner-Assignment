<?php

return [
    'cache_ttl' => (int) env('SHORT_URL_CACHE_TTL', 86400),
    'code_length' => (int) env('SHORT_URL_CODE_LENGTH', 7),
];
