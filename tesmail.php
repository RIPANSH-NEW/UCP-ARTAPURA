<?php
$password_input = 'panz12321'; // Misal: rahasia123
$salt = '0b7607f18317e039';
$hash_input = strtoupper(hash('sha256', $password_input . $salt));
echo $hash_input;