<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'update_payment') {
    $year = intval($input['year']);
    $month = intval($input['month']);
    $is_paid = intval($input['is_paid']);

    $stmt = $pdo->prepare("INSERT INTO reservations (year, month, is_paid) VALUES (?, ?, ?) 
                           ON CONFLICT(year, month) DO UPDATE SET is_paid = ?");
    $stmt->execute([$year, $month, $is_paid, $is_paid]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'save_month') {
    $year = intval($input['year']);
    $month = intval($input['month']);
    $name = trim($input['name'] ?? '');
    $reservation_date = trim($input['reservation_date'] ?? '');
    $note = trim($input['note'] ?? '');

    // Ako su sva polja prazna, možemo opcionalno da obrišemo zapis ili ga ostavimo praznim
    $stmt = $pdo->prepare("INSERT INTO reservations (year, month, name, reservation_date, note) VALUES (?, ?, ?, ?, ?) 
                           ON CONFLICT(year, month) DO UPDATE SET name = ?, reservation_date = ?, note = ?");
    $stmt->execute([$year, $month, $name, $reservation_date, $note, $name, $reservation_date, $note]);
    
    echo json_encode(['success' => true, 'message' => 'Promena je uspešno sačuvana!']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Nepoznata akcija.']);
