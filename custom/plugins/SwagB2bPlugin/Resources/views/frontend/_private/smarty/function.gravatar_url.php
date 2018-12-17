<?php declare(strict_types=1);

const GRAVATAR_BASE_URL = 'https://www.gravatar.com/avatar/';

const GRAVATAR_SIZE = 400;

/**
 * Returns the Gravatar URL for the given email.
 *
 * @param $params
 * @return string
 */
function smarty_function_gravatar_url($params)
{
    if (!isset($params['email'])) {
        throw new \InvalidArgumentException('Missing required parameter "email"');
    }
    $email = $params['email'];

    $url = GRAVATAR_BASE_URL . md5($email) . '?s=' . GRAVATAR_SIZE;

    return $url;
}
