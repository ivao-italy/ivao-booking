<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once "../models/db.php"; //TO BE FIXED WITH AUTOLOAD USING SAME HOSTING CONFIG OF PROD
// (must be top level and not in a subfolder... then rebuild autoloader with composer dumpautoloader -o)

use App\Models\Db;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

const API_KEY = '';

$app = AppFactory::create();
$app->setBasePath('/api');

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get("/flights/{dof}/{callsign}/", function (Request $request, Response $response, array $args) {
    if ($request->getHeaderLine('X-Api-Key') != API_KEY) {
        return $response->withStatus(401);
    }

    $params = array(":callsign" => $args['callsign'], ":dof" => $args['dof']);

    $sql = "SELECT f.callsign, f.booked_by, f.aircraft_icao, f.gate 
            FROM flights f
            LEFT OUTER JOIN airports as dep_apt ON f.origin_icao = dep_apt.icao
            WHERE f.callsign = :callsign
            AND DATE_FORMAT(CASE WHEN dep_apt.icao IS NOT NULL THEN f.departure_time ELSE f.arrival_time END, '%Y%m%d') = :dof";

    try {
        $db = new Db();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($customers));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


$app->run();