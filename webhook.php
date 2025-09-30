<?php
// webhook.php - Recibe notificaciones de Mercado Pago

// Configuración
$access_token = 'APP_USR-5726001139090018-082008-c9a942225ec19ee0ea666d2a1dc236d5-188036360';

// Conexión a la base de datos (HARD-CODE)
$host     = "66.97.43.58";
$dbname   = "testwebhook";
$username = "gaston";
$password = "campo40164234";
$port     = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Recibir notificación de Mercado Pago
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log para debug (opcional)
file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

// Verificar si es una notificación de pago
if (isset($data['type']) && $data['type'] == 'payment') {
    
    $payment_id = $data['data']['id'];
    
    // Consultar información del pago a Mercado Pago
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
    
    // Verificar si el pago está aprobado
    if ($payment['status'] == 'approved') {
        
        // Preparar datos para insertar
        $id_pago = $payment['id'];
        $monto   = $payment['transaction_amount'];
        $fecha   = date('Y-m-d H:i:s', strtotime($payment['date_approved']));
        $metodo  = $payment['payment_method_id'];
        $estado  = $payment['status'];
        
        // Verificar si el pago ya existe
        $check = $pdo->prepare("SELECT id FROM pagos WHERE id_pago = ?");
        $check->execute([$id_pago]);
        
        if ($check->rowCount() == 0) {
            // Insertar en la base de datos
            $sql = "INSERT INTO pagos (id_pago, monto, fecha, metodo, estado) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_pago, $monto, $fecha, $metodo, $estado]);
            
            // Log de éxito
            file_put_contents('mp_log.txt', date('Y-m-d H:i:s') . " - Pago aprobado e insertado: $id_pago\n", FILE_APPEND);
        }
    }
}

http_response_code(200);
echo "OK";
