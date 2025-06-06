<?php

require_once '../vendor/autoload.php';

use Casbin\Enforcer;
use Casbin\Util\Log;
use CasbinAdapter\Database\Adapter as DatabaseAdapter;

$config = [
        'type' => 'mysql', // mysql,pgsql,sqlite,sqlsrv
        'hostname' => '127.0.0.1',
        'database' => 'new_bdd',
        'username' => 'root',
        'password' => '',
        'hostport' => '3306',
];

$adapter = DatabaseAdapter::newAdapter($config);

$e = new Enforcer('../src/Auth/casbin.conf', $adapter);

$sub = json_encode(["name" => "julien", "role" => "admin"]); // the user that wants to access a resource.
$obj = json_encode(["name" => "chat", "owner" => "julien"]); // the resource that is going to be accessed.
$act = "delete"; // the operation that the user performs on the resource.
$attr = "domain1";
if ($e->enforce($sub, $obj, $act, $attr) === true) {
        // permit alice to read data1
        echo "ok";
} else {
        echo "denied";
        // deny the request, show an error
}