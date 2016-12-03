<?php

function autoload_code($class_name)
{
    $file = "../code/$class_name.php";
    if (file_exists($file))
    {
        require_once($file);
        return true;
    }
    return false;
}

function autoload_code_objects($class_name)
{
    $file = "../code/objects/$class_name.php";
    if (file_exists($file))
    {
        require_once($file);
        return true;
    }
    return false;
}

function autoload_root($class_name)
{
    $file = "$class_name.php";
    if (file_exists($file))
    {
        require_once($file);
        return true;
    }
    return false;
}

function autoload($class_name)
{
    if (autoload_code($class_name))
        return;
    if (autoload_code_objects($class_name))
        return;
    if (autoload_root('../' . $class_name))
        return;
    if (autoload_root($class_name))
        return;
    require_once("$class_name.php");
}

spl_autoload_register('autoload');

?>
