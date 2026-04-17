<?php
require_once 'configs/env.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN refund_amount DECIMAL(15,2) DEFAULT 0 AFTER final_price");
        echo "Added refund_amount column.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "refund_amount already exists.\n";
        } else {
            echo "Error adding refund_amount: " . $e->getMessage() . "\n";
        }
    }

    try {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN refund_percentage INT DEFAULT 0 AFTER refund_amount");
        echo "Added refund_percentage column.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "refund_percentage already exists.\n";
        } else {
            echo "Error adding refund_percentage: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
