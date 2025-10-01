<?php
// consultar_pagos.php - API simple para Visual FoxPro

//header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos (HARD-CODE)
//$host     = "66.97.43.58";
//$dbname   = "testing";
//$username = "gaston";
//$password = "campo40164234";
//$port     = 3306;

//try {
  //  $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
   // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//} catch (PDOException $e) {
 //   die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
//}

// Parámetros opcionales
//$id_pago = isset($_GET['id_pago']) ? $_GET['id_pago'] : null;
//$fecha   = isset($_GET['fecha']) ? $_GET['fecha'] : null;
//$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

// Consultar pagos
//$sql    = "SELECT * FROM pagos WHERE 1=1";
//$params = [];

//if ($id_pago) {
  //  $sql .= " AND id_pago = ?";
    //$params[] = $id_pago;
//}

//if ($f//echa) {
  //  $sql .= " AND DATE(fecha) = ?";
   // $params[] = $fecha;
//}

//$sql .= " ORDER BY fecha DESC LIMIT ?";
//$params[] = $limit;

//$stmt = $pdo->prepare($sql);
//$stmt->execute($params);
//$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

//echo json_encode([
  //  'success' => true,
   // 'total'   => count($pagos),
    //'pagos'   => $pagos
//], JSON_PRETTY_PRINT);




header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos (HARD-CODE)
$host     = "66.97.43.58";
$dbname   = "testing";
$username = "gaston";
$password = "campo40164234";
$port     = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

// Parámetros opcionales
$id_pago = isset($_GET['id_pago']) ? $_GET['id_pago'] : null;
$fecha   = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 100; // fuerza a entero

// Consultar pagos
$sql    = "SELECT * FROM pagos WHERE 1=1";
$params = [];

if ($id_pago) {
    $sql .= " AND id_pago = ?";
    $params[] = $id_pago;
}

if ($fecha) {
    $sql .= " AND DATE(fecha) = ?";
    $params[] = $fecha;
}

// acá insertamos el número directo, no como parámetro
$sql .= " ORDER BY fecha DESC LIMIT $limit";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'total'   => count($pagos),
    'pagos'   => $pagos
], JSON_PRETTY_PRINT);
