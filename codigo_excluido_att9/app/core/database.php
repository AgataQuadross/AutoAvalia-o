<?php

require __DIR__ . '/config.php';

function iniciarPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $conexaoBd = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            // Erros de banco viram exceções — mais fácil de tratar
            // (e mais seguro do que deixar dado vazar em warning solto).
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // fetch() já retorna array associativo por padrão.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Prepared statements DE VERDADE (não emulados pelo PHP),
            // essencial para a proteção contra SQL Injection que vimos
            // no Encontro 3.
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($conexaoBd, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

?>