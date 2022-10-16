<?php

require_once __DIR__ . '/../src/Lib/Psr4AutoloaderClass.php';
// instantiate the loader
$loader = new App\Vote\Lib\Psr4AutoloaderClass();
// register the base directories for the namespace prefix
$loader->addNamespace('App\Vote', __DIR__ . '/../src');
// register the autoloader
$loader->register();

if (isset($_GET['controller'])) {
    $controller = $_GET['controller'];
} else {
    $controller = "voiture";
}
$controllerClassName = 'App\Vote\Controller\Controller' . ucfirst($controller);

//if (isset($_GET['action'])) {
//    $action = $_GET['action'];
//} else {
//    $action = "readAll";
//}
//if (class_exists($controllerClassName) && in_array($action, get_class_methods($controllerClassName))) {
//    $controllerClassName::$action();
//}
//else{
//    controllerVoiture::error();
//}