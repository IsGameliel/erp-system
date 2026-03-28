<?php

return [
    'owner_host' => env('OWNER_HOST', 'group.codewitheugene.com'),
    'owner_path_prefix' => env('OWNER_PATH_PREFIX', 'erp'),
    'local_hosts' => array_filter(array_map('trim', explode(',', env('OWNER_LOCAL_HOSTS', '127.0.0.1,localhost')))),
];
