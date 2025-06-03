<?php
session_start();

// Clear WhatsApp data from session
if (isset($_SESSION['whatsapp_data'])) {
    unset($_SESSION['whatsapp_data']);
    echo json_encode(['status' => 'success', 'message' => 'WhatsApp data cleared']);
} else {
    echo json_encode(['status' => 'info', 'message' => 'No WhatsApp data to clear']);
}
?>