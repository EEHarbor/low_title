<?php

/**
 * Resolve the EE super object or an explicitly configured service.
 *
 * @param string|null $service
 * @param mixed ...$arguments
 * @return mixed
 * @throws RuntimeException
 */
function ee($service = null, ...$arguments)
{
    $environment = $GLOBALS['low_title_ee'];
    if ($service === null) {
        return $environment;
    }

    if ($service === 'db') {
        return $environment->db->newQuery();
    }

    if (isset($environment->services[$service])) {
        return $environment->services[$service] instanceof Closure ? call_user_func_array($environment->services[$service], $arguments) : $environment->services[$service];
    }

    throw new RuntimeException('No EE service configured: ' . $service);
}

/**
 * Read a cache file with the EE file helper's false-on-missing contract.
 *
 * @param string $path
 * @return string|false
 */
function read_file($path)
{
    return is_file($path) ? file_get_contents($path) : false;
}

/**
 * Write a cache file with the EE file helper's boolean contract.
 *
 * @param string $path
 * @param string $data
 * @return bool
 */
function write_file($path, $data)
{
    if (is_dir($path) || (is_file($path) && !is_writable($path)) || !is_writable(dirname($path))) {
        return false;
    }

    return file_put_contents($path, $data) !== false;
}

/**
 * Collapse doubled URL slashes using EE's string helper contract.
 *
 * @param string $value
 * @return string
 */
function reduce_double_slashes($value)
{
    return preg_replace('#(^|[^:])/+#', '$1/', $value);
}

/**
 * Return the active URI path supplied by EE.
 *
 * @return string
 */
function uri_string()
{
    return ee()->uri->uri_string();
}

/**
 * Produce a URL title using EE's helper shape for bridge delegation assertions.
 *
 * @param string $value
 * @return string
 */
function url_title($value)
{
    return str_replace(' ', '-', $value);
}

/**
 * Prefix schemeless URLs through the legacy URL helper contract.
 *
 * @param string $value
 * @return string
 */
function prep_url($value)
{
    return 'https://' . $value;
}

/**
 * Record a form action while isolating EE's external form-helper rendering.
 *
 * @param string $action
 * @param array $attributes
 * @return string
 */
function form_open($action, $attributes)
{
    $GLOBALS['low_title_form_calls'][] = array('open', $action, $attributes);

    return '<form>';
}

/**
 * Record a form input's bound value.
 *
 * @param string $name
 * @param mixed $value
 * @return string
 */
function form_input($name, $value)
{
    $GLOBALS['low_title_form_calls'][] = array('input', $name, $value);

    return '<input>';
}

/**
 * Record a form checkbox's name, value, and checked state.
 *
 * @param string $name
 * @param string $value
 * @param bool $checked
 * @return string
 */
function form_checkbox($name, $value, $checked)
{
    $GLOBALS['low_title_form_calls'][] = array('checkbox', $name, $value, $checked);

    return '<input type="checkbox">';
}

/**
 * Record the end of a form.
 *
 * @return string
 */
function form_close()
{
    $GLOBALS['low_title_form_calls'][] = array('close');

    return '</form>';
}
