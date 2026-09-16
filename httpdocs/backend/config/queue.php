<?php
return ['default' => env('QUEUE_CONNECTION', 'redis'), 'connections' => ['sync' => ['driver' => 'sync'], 'redis' => ['driver' => 'redis', 'connection' => 'default', 'queue' => env('REDIS_QUEUE', 'default'), 'retry_after' => 210, 'block_for' => 5, 'after_commit' => false]], 'failed' => ['driver' => 'database-uuids', 'database' => 'mysql', 'table' => 'failed_jobs']];
