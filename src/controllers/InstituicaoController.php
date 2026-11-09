<?php

// controllers/InstituicaoController.php
require_once __DIR__ . '/../models/Instituicao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instituicao = new Instituicao();

    $fotoNome = $_POST['foto_existente'] ?? ''; // caso não faça upload

    // Verificar se há uma nova foto para upload
    if (!empty($_FILES['foto']['name'])) {
        // Gerar nome único para a foto
        $fotoNome = time() . '_' . $_FILES['foto']['name'];
        // Definir o caminho para armazenar a foto
        $targetDir = __DIR__ . '/../uploads/';
        $targetFile = $targetDir . basename($fotoNome);

        // Move o arquivo da pasta temporária para o diretório de upload
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            // Foto movida com sucesso
        } else {
            // Erro ao mover o arquivo
            die('Erro ao fazer upload da foto.');
        }
    }

    $dados = [
        'id_instituicoes' => $_POST['id_instituicoes'],
        'nome' => $_POST['nome'],
        'responsavel' => $_POST['responsavel'],
        'endereco' => $_POST['endereco'],
        'pais' => $_POST['pais'],
        'cidade' => $_POST['cidade'],
        'provincia' => $_POST['provincia'],
        'email' => $_POST['email'],
        'telefone' => $_POST['telefone'],
        'telemovel' => $_POST['telemovel'],
        'fax' => $_POST['fax'],
        'website' => $_POST['website'],
        'foto' => $fotoNome,
        'nuit' => $_POST['nuit'],
        'tipo_instituicao' => $_POST['tipo_instituicao']
    ];

    // Atualiza a instituição no banco de dados
    if ($instituicao->atualizarInstituicao($dados)) {
        header('Location: ../views/configuracoes.php?sucesso=1');
        exit;
    } else {
        header('Location: ../views/configuracoes.php?erro=1');
        exit;
    }
}


