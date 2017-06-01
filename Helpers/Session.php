<?php

namespace Helpers;

class Session
{
    private $isLoaded = false;

    private function __construct() {
        session_name('battle_ships_game');
    }

    private static $_instance = null;

    public static function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function start() {

        if ($this->isStarted() == true) {
            $this->load();

            return;
        }

        session_start();

        $this->load();
    }

    public function isStarted() {
        return session_id() !== '';
    }

    public function isLoaded() {
        return $this->isLoaded;
    }

    public function load() {
        if ($this->isLoaded() == true) {

            return;
        }

        if ($this->isStarted() == false) {
            $_SESSION = array();

            $this->isLoaded = false;

            return;
        }

        $this->isLoaded = true;
    }

    private $isClosed = false;

    public function set($key, $value) {

        if ($this->isClosed == true) {
            throw new \Exception('Session has been closed.');
        }

        $this->start();

        if ($value === null) {
            unset($_SESSION[$key]);
        }
        else {
            $_SESSION[$key] = $value;
        }
    }

    public function exists($key) {
        $this->start();

        if ($this->isStarted() && isset($_SESSION[$key]) == true) {
            return true;
        }

        return false;
    }

    public function get($key, $defaultValue = null) {

        $value = $defaultValue;
        if ($this->exists($key) == true) {
            $value = $_SESSION[$key];
        }

        return $value;
    }

    public function delete($key) {
        $this->set($key, null);
    }

    public function clear() {
        session_destroy();

        $this->isClosed = true;
    }

}