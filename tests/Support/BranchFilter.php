<?php

$sourceRoot = dirname(__DIR__, 2) . '/low_title/';
xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, array($sourceRoot, 'phar://' . $sourceRoot . 'low_title.phar/'));
