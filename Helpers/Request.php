<?php

namespace Helpers;

class Request
{

    const VAR_POST = '_POST';
    const VAR_GET = '_GET';
    const VAR_SERVER = '_SERVER';

    private $_variables;

    public function __construct()
    {
        $this->_variables = array(self::VAR_POST => &$_POST, self::VAR_GET => &$_GET, self::VAR_SERVER => &$_SERVER);
    }

    public function isCLI()
    {
        return php_sapi_name() === PHP_CLI;
    }

    public function isPOSTRequest()
    {
        return $this->server('REQUEST_METHOD') == 'POST';
    }

    public function server($varName = null, $defaultValue = null)
    {
        return $this->getRequestValue(self::VAR_SERVER, $varName, $defaultValue);
    }

    public function arrayGET()
    {
        return $this->_variables[self::VAR_GET];
    }

    public function arrayPOST()
    {
        return $this->_variables[self::VAR_POST];
    }
    public function get($varName = null, $defaultValue = null)
    {
        return $this->getRequestValue(self::VAR_GET, $varName, $defaultValue);
    }

    public function post($varName = null, $defaultValue = null)
    {
        return $this->getRequestValue(self::VAR_POST, $varName, $defaultValue);
    }

    protected function getRequestValue($source, $varName, $defaultValue)
    {

        if (isset($this->_variables[$source]) == true) {

            if ($varName === null) {
                return $GLOBALS[$source];
            } elseif (isset($this->_variables[$source][$varName]) == true) {
                return $this->_variables[$source][$varName];
            } else {
                return $defaultValue;
            }
        } else {
            throw new \Exception('"' . $source . '" is undefined.');
        }
    }

}