<?php
// init.php

// Inicia a sessão apenas uma vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função global para obter o usuário logado
function usuarioLogado()
{
    return $_SESSION['nome'] ?? 'Sistema';
}
