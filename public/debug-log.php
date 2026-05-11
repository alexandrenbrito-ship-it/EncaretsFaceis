<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

function erroPersonalizado($errno, $errstr, $errfile, $errline) {
    $mensagem = date('Y-m-d H:i:s') . " - Erro: $errstr | Arquivo: $errfile | Linha: $errline\n";
    file_put_contents(__DIR__ . '/error.log', $mensagem, FILE_APPEND);
    return true;
}

set_error_handler('erroPersonalizado');

echo json_encode([
    'status' => 'ok',
    'mensagem' => 'Log de erros configurado',
    'timestamp' => date('Y-m-d H:i:s')
]);