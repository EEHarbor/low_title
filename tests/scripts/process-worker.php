<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
if (!posix_setpgid(0, 0)) {
    throw new RuntimeException('Cannot isolate the native process group');
}
file_put_contents($argv[1], (string) getmypid());

$command = array_slice($argv, 2);
if (!$command) {
    throw new RuntimeException('A native process command is required');
}

pcntl_exec($command[0], array_slice($command, 1));
throw new RuntimeException('Cannot execute the native process command');
