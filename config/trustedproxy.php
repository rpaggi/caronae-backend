<?php

return [

 
    'proxies' => '*',

    /*
     * Default Header Names
     *
     * Change these if the proxy does
     * not send the default header names.
     *
     * Note that headers such as X-Forwarded-For
     * are transformed to HTTP_X_FORWARDED_FOR format.
     *
     * The following are Symfony defaults, found in
     * \Symfony\Component\HttpFoundation\Request::$trustedHeaders
     *
     * You may optionally set headers to 'null' here if you'd like
     * for them to be considered untrusted instead. Ex:
     *
     * Illuminate\Http\Request::HEADER_CLIENT_HOST  => null,
     * 
     * WARNING: If you're using AWS Elastic Load Balancing or Heroku,
     * the FORWARDED and X_FORWARDED_HOST headers should be set to null 
     * as they are currently unsupported there.
     */
    'headers' => [
        (defined('Illuminate\Http\Request::HEADER_FORWARDED') ? Illuminate\Http\Request::HEADER_FORWARDED : 'forwarded') => null,
        Illuminate\Http\Request::HEADER_CLIENT_IP    => 'X_FORWARDED_FOR',
        Illuminate\Http\Request::HEADER_CLIENT_HOST  => null,
        Illuminate\Http\Request::HEADER_CLIENT_PROTO => 'X_FORWARDED_PROTO',
        Illuminate\Http\Request::HEADER_CLIENT_PORT  => 'X_FORWARDED_PORT',
    ]
];
