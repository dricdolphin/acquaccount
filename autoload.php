<?php
    spl_autoload_register(function ($class) {
        include 'src/' . $class . '.php';
    });
?>