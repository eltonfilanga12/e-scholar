<?php
class LogHelper
{
    public static function registrar($pdo, $usuario, $acao, $descricao)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql = "INSERT INTO logs (usuario, acao, descricao, ip) VALUES (:usuario, :acao, :descricao, :ip)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'usuario' => $usuario,
            'acao' => $acao,
            'descricao' => $descricao,
            'ip' => $ip
        ]);
    }
}
