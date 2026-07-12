<?php

class AuthController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }
}
