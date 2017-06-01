<?php

namespace Helpers;

class TemplateEngine {

    protected static $_instance = null;

    private function __construct()
    {


    }

    public static function getInstance()
    {

        if(self::$_instance == null) {

            self::$_instance = new self();
        }

        return self::$_instance;

    }

    public function render($filename, $data = array())
    {
        $fileFullpath = VIEWS.DIRECTORY_SEPARATOR.$filename.".php";

        if (!is_file($fileFullpath)) {
            throw new \Exception('Template file not found');
        }

        extract($data, EXTR_SKIP);

        ob_start();

        require_once $fileFullpath;

        return ob_get_clean();
    }

}
