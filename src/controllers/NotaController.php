<?php
// /src/controllers/NotaController.php

require_once __DIR__ . '/../models/Nota.php';

class NotaController
{
    private $notaModel;

    public function __construct()
    {
        $this->notaModel = new Nota();
    }

    // Exibir todas as notas por nome do usuário (GET)
    public function buscarNotasPorUsuario($usuario)
    {
        return $this->notaModel->buscarNotasPorUsuario($usuario);
    }

    // Atualizar um campo específico de uma nota (POST)
    public function atualizarNota($id, $campo, $valor)
    {
        return $this->notaModel->atualizarNota($id, $campo, $valor);
    }

    // Buscar notas por disciplina (GET)
    public function buscarNotasPorDisciplina($disciplina_id)
    {
        return $this->notaModel->buscarNotasPorDisciplina($disciplina_id);
    }

    // Buscar notas por aluno e disciplina (GET)
    public function getNotasPorAlunoEDisciplina($alunoId, $disciplinaId)
    {
        return $this->notaModel->getNotasPorAlunoEDisciplina($alunoId, $disciplinaId);
    }

    // Atualizar todas as notas de um aluno para uma disciplina (POST)
    public function atualizarNotas($alunoId, $disciplinaId, $teste1, $teste2, $teste3)
    {
        return $this->notaModel->atualizarNotas($alunoId, $disciplinaId, $teste1, $teste2, $teste3);
    }

    // Buscar alunos por classe e turma (GET)
    public function buscarAlunosPorClasseETurma($classeId, $turmaId)
    {
        return $this->notaModel->buscarAlunosPorClasseETurma($classeId, $turmaId);
    }

    // Buscar notas por disciplina, turma e classe (GET)
    public function buscarNotasPorDisciplinaETurmaEClasse($disciplinaId, $turmaId, $classeId)
    {
        return $this->notaModel->buscarNotasPorDisciplinaETurmaEClasse($disciplinaId, $turmaId, $classeId);
    }
}
