<?php
// Configuración de Mercado Pago
$access_token = 'APP_USR-5726001139090018-082008-c9a942225ec19ee0ea666d2a1dc236d5-188036360';

// Conexión a la base de datos
$host     = "66.97.43.58";
$dbname   = "testing";
$username = "gaston";
$password = "campo40164234";
$port     = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    file_put_contents('mp_log.txt', "Error de conexión DB: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    exit("DB connection error");
}

// Recibir notificación de Mercado Pago
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// Log de entrada (debug)
file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - Notificación: " . $input . "\n", FILE_APPEND);

if (isset($data['type']) && $data['type'] === 'payment' && isset($data['data']['id'])) {
    $payment_id = $data['data']['id'];

    // Consultar API de Mercado Pago para obtener info completa del pago
    $url = "https://api.mercadopago.com/v1/payments/$payment_id";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $payment = json_decode($response, true);

    // Insertar/actualizar en la base si está aprobado
    if (isset($payment['status']) && $payment['status'] === 'approved') {
        $id_pago     = $payment['id'];
        $monto       = $payment['transaction_amount'];
        $fecha       = isset($payment['date_approved']) ? date('Y-m-d H:i:s', strtotime($payment['date_approved'])) : null;
        $metodo      = $payment['payment_method_id'];
        $estado      = $payment['status'];
        $pos_id      = isset($payment['pos_id']) ? $payment['pos_id'] : null;
        $store_id    = isset($payment['store_id']) ? $payment['store_id'] : null;
        $descripcion = isset($payment['description']) ? $payment['description'] : null;
        $raw         = json_encode($payment);

        $sql = "INSERT INTO pagos (id_pago, monto, fecha, metodo, estado, pos_id, store_id, descripcion, raw)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    monto=VALUES(monto),
                    fecha=VALUES(fecha),
                    metodo=VALUES(metodo),
                    estado=VALUES(estado),
                    pos_id=VALUES(pos_id),
                    store_id=VALUES(store_id),
                    descripcion=VALUES(descripcion),
                    raw=VALUES(raw)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_pago, $monto, $fecha, $metodo, $estado, $pos_id, $store_id, $descripcion, $raw]);

        file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - Pago insertado/actualizado: $id_pago\n", FILE_APPEND);
    } else {
        file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - Pago no aprobado o inválido\n", FILE_APPEND);
    }
} else {
    file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - Notificación ignorada (no es pago)\n", FILE_APPEND);
}

http_response_code(200);
echo "OK";
?>