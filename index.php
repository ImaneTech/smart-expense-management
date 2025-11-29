<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";

$uri = explode("/", trim($_SERVER["REQUEST_URI"], "/"));

$endpoint = $uri[1] ?? null;
$method = $_SERVER["REQUEST_METHOD"];

switch ($endpoint) {

    case "auth":
        require "routes/auth.php";
        break;

    case "categorie":
        require "routes/categorie.php";
        break;

    case "demande":
        require "routes/demande.php";
        break;

    case "details":
        require "routes/details.php";
        break;

    default:
        echo json_encode(["error" => "Route introuvable"]);
        break;
}
