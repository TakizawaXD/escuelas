<?php
// /modules/calendar/events.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    // Retornar eventos en formato FullCalendar
    $stmt = $db->query("SELECT id, title, start_date as start, end_date as end, color FROM calendar_events");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear para FullCalendar (remover end si está vacío)
    $formatted = array_map(function($e) {
        if (empty($e['end'])) {
            unset($e['end']);
        }
        return $e;
    }, $events);
    
    echo json_encode($formatted);
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
        echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $start = $data['start'] ?? '';
    $end = !empty($data['end']) ? $data['end'] : null;
    $color = $data['color'] ?? '#4f46e5';
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($start)) {
        echo json_encode(['success' => false, 'error' => 'Título e inicio son obligatorios']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO calendar_events (title, start_date, end_date, color, user_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $start, $end, $color, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar en DB']);
    }
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
        echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
        exit;
    }

    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("DELETE FROM calendar_events WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    }
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
