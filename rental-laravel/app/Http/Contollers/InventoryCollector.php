<?php
namespace App\Http\Controllers;

class InventoryController {
    public function index() {
        return json_encode([
            'success' => true,
            'framework' => 'Laravel (Migrated Module)',
            'data' => []
        ]);
    }
}