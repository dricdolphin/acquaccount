<?php
    spl_autoload_register(function ($class) {
        if (str_contains($class, "acquaccount\\")) {
            $class = substr($class, 12);
            include 'src/' . $class . '.php';
        }
    });
?>