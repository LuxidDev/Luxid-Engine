<?php

namespace Luxid\Http;

class Request
{
    public function getPath()
    {
        $path = $_SERVER["REQUEST_URI"] ?? '/';
        $position = strpos($path, '?');

        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    public function method()
    {
        // Support method override via _method parameter
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        // Check for method overrid in POST data
        if ($method === 'post' && isset($_POST['method'])) {
            return strtolower($_POST['_method']);
        }

        // Check for method override in headers
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtolower($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        return $method;
    }

    public function getBody()
    {
        $body = [];
        $method = $this->method();

        /**
            have a look in the Super Global 'GET' and 'POST
            find the key, take the value
            remove invalid chars and insert into the body
        */
        if ($method === 'get') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        } else {
            // Handle POST, PUT, PATCH, DELETE

            // First check if it's JSON input
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $rawInput = file_get_contents('php://input');

            if (strpos($contentType, 'application/json') !== false) {
                // JSON input
                $jsonData = json_decode($rawInput, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $body = $jsonData;
                }
            } else {
                // Form data
                if ($method === 'post') {
                    foreach ($_POST as $key => $value) {
                        $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }

                // Also parse raw input for PUT/PATCH/DELETE
                if (!empty($rawInput) && empty($body)) {
                    parse_str($rawInput, $parsed);
                    foreach ($parsed as $key => $value) {
                        $body[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            }
        }

        return $body;
    }

    // Utility Methods (helpers) ================
    public function isGet()
    {
        return $this->method() === 'get';
    }

    public function isPost()
    {
        return $this->method() === 'post';
    }

    public function isPut()
    {
        return $this->method() === 'put';
    }

    public function isPatch()
    {
        return $this->method() === 'patch';
    }

    public function isDelete()
    {
        return $this->method() === 'delete';
    }

    public function isJson()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    public function getJson()
    {
        $rawInput = file_get_contents('php://input');
        return json_decode($rawInput, true);
    }

}
