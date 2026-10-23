<?php

namespace LowTitleTests;

class Process
{
    /**
     * Run an owned process with independent output streams and a finite deadline.
     *
     * @param array $command
     * @param string $directory
     * @param array $environment
     * @param float $timeout
     * @return array
     * @throws \RuntimeException
     */
    public static function run(array $command, $directory, array $environment, $timeout = 2.0)
    {
        $output = tmpfile();
        $errors = tmpfile();
        $process = null;
        $control = tmpfile();
        $pid = null;
        $ownedGroup = false;
        if ($output === false || $errors === false || $control === false) {
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_resource($errors)) {
                fclose($errors);
            }
            if (is_resource($control)) {
                fclose($control);
            }
            throw new \RuntimeException('Cannot allocate native process output files');
        }
        try {
            $controlPath = stream_get_meta_data($control)['uri'];
            $wrapper = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', dirname(__DIR__) . '/scripts/process-worker.php', $controlPath);
            $process = proc_open(array_merge($wrapper, $command), array(0 => array('pipe', 'r'), 1 => $output, 2 => $errors), $pipes, $directory, $environment);
            if (!is_resource($process)) {
                throw new \RuntimeException('Cannot start the native process');
            }
            fclose($pipes[0]);
            $deadline = hrtime(true) + $timeout * 1000000000;
            $timedOut = false;
            do {
                $status = proc_get_status($process);
                $pid = $status['pid'];
                rewind($control);
                $marker = stream_get_contents($control);
                $ownedGroup = $marker === (string) $pid;
                if (!$status['running']) {
                    break;
                }
                if (hrtime(true) >= $deadline) {
                    $timedOut = true;
                    self::stop($process, $pid, $ownedGroup);
                    $status = proc_get_status($process);
                    break;
                }
                usleep(10000);
            } while (true);
            if ($ownedGroup) {
                posix_kill(-$pid, 9);
            }
            $closed = proc_close($process);
            $process = null;
            $exit = $status['exitcode'] >= 0 ? $status['exitcode'] : $closed;
            rewind($output);
            rewind($errors);

            return array('exit' => $timedOut ? 124 : $exit, 'stdout' => stream_get_contents($output), 'stderr' => stream_get_contents($errors), 'timed_out' => $timedOut, 'pid' => $pid);
        } finally {
            if (is_resource($process)) {
                self::stop($process, $pid, $ownedGroup);
                proc_close($process);
            }
            fclose($output);
            fclose($errors);
            if (is_resource($control)) {
                fclose($control);
            }
        }
    }

    /**
     * Terminate the owned child and its confirmed process group before reaping.
     *
     * @param resource $process
     * @param int|null $pid
     * @param bool $ownedGroup
     * @return void
     */
    private static function stop($process, $pid, $ownedGroup)
    {
        foreach (array(15, 9) as $signal) {
            if ($ownedGroup && $pid !== null) {
                posix_kill(-$pid, $signal);
            }
            proc_terminate($process, $signal);
            $deadline = hrtime(true) + 250000000;
            do {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    if ($ownedGroup && $pid !== null) {
                        posix_kill(-$pid, 9);
                    }
                    return;
                }
                usleep(10000);
            } while (hrtime(true) < $deadline);
        }
    }
}
