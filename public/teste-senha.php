<?php
$senha = 'admin123';
$hash = '$2y$10$TKh8H1.P4K1pW.ZvF1jfV.y2vPVHzF.jG3eUzKoXaHcHibjgQiE9e';

if (password_verify($senha, $hash)) {
    echo "Senha correta!";
} else {
    echo "Senha incorreta!";
}
