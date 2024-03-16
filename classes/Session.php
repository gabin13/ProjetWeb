<?php


namespace App;

class Session
{
    function __construct()
    {
        
    }

    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function add(string $key, $data)
    {
        $_SESSION[$key] = $data;
    }

    public function get(string $key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    public function destroy()
    {
        unset($_SESSION);
        session_destroy();
    }

    public function isConnected()
    {
        return isset($_SESSION['user']);
    }

    public function hasRole(string $role)
    {
        return $_SESSION['user']['role'] == $role ? true : false;
    }

}
