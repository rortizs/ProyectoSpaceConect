<?php

include 'Router.php';
include '../NetworkUtils/utils.php';

$router = new Router('200.215.233.18', '18007', 'Suportecnetwork', 'Baldomera1991@#');

$res = $router->RequestBuilder("interface", "GET");

// foreach ($res->data as $k => $ki) {
//     if (str_contains($ki->name, "<pppoe-")) {
//         $net_name = str_replace(["<pppoe-", ">"], "", $ki->name);

//         echo $net_name . "<br><br>";

//         // $c = sqlObject("SELECT * FROM clients WHERE net_name = '$net_name'");

//         // $his = (object) array();

//         // $his->clientid = $c->id;
//         // $his->diff = 0;
//         // $his->currenttx = $ki->{"tx-byte"};
//         // $his->currentrx = $ki->{"rx-byte"};
//         // $his->date = time();

//         // sqlInsert("network_clients_usage", $his);
//     }
// }

////

// header('Content-Type: application/json');
// echo json_encode($res);
