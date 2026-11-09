<?php
$exibirExames = false;

require_once __DIR__ . '/../../../config/database.php';

// Verificar se o usuário é Aluno e se deve exibir exames
if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Aluno' && isset($_SESSION['user_id'])) {
    $database = new Database();
    $pdo = $database->getConnection();

    if ($pdo) {
        $aluno_id_usuario = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT classe_id FROM aluno WHERE id_usuario = ?");
        $stmt->execute([$aluno_id_usuario]);
        $classe_id = $stmt->fetchColumn();

        if ($classe_id) {
            $stmt = $pdo->prepare("SELECT tem_exame FROM classe WHERE id_classe = ?");
            $stmt->execute([$classe_id]);
            $tem_exame = $stmt->fetchColumn();

            if (strtolower($tem_exame) === 'sim') {
                $exibirExames = true;
            }
        }
    }
}
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <!-- ADMINISTRADOR -->
                <?php if ($_SESSION['tipo'] === 'Administrador') : ?>
                    <li class="menu-title"><span>Principal</span></li>
                    <li><a href="dashboard"><i class="la la-dashboard"></i> <span>Dashboard</span></a></li>

                    <li class="menu-title"><span>Gestão Acadêmica</span></li>
                    <li class="submenu">
                        <a href="#"><i class="la la-user-graduate"></i> <span>Alunos</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="lista_aluno"><i class="la la-clipboard-check"></i> <span>Lista de Alunos</span> </a></li>
                            <li><a href="lancar_notas"><i class="la la-clipboard-list"></i> <span>Notas de Frequência</span></a></li>
                            <li><a href="lancar_notas_exame"><i class="la la-file-excel"></i> <span>Exames</span></a></li>
                            <li><a href="matricula"><i class="la la-user-plus"></i> <span>Matrícula</span></a></li>
                            <li><a href="atribuir_turma"><i class="la la-random"></i> <span>Atribuir Turma</span></a></li>
                            <li><a href="justificativas_aprovadas"><i class="la la-check-square"></i> <span>Controle de justificativas</span></a></li>
                            <li><a href="controle_falta"><i class="la la-calendar-times"></i> <span>Faltas dos Alunos</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="la la-chalkboard-teacher"></i> <span>Professores</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="professores"><i class="la la-chalkboard-teacher"></i> <span>Cadastrar Professores</span></a></li>
                            <li><a href="atribuir_turma_classe.php"><i class="la la-tasks"></i> <span>Atribuir T/C</span></a></li>
                        </ul>
                    </li>

                    <li><a href="gerir_solicitacoes.php"><i class="la la-edit"></i> <span>Gerir Solicitações</span></a></li>

                    <li class="submenu">
                        <a href="#"><i class="la la-cogs"></i> <span>Configurações</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="funcionarios"><i class="la la-users-cog"></i> <span>Funcionários</span></a></li>
                            <li><a href="turmas"><i class="la la-layer-group"></i> <span>Turmas</span></a></li>
                            <li><a href="disciplinas"><i class="la la-book-open"></i> <span>Disciplinas</span></a></li>
                            <li><a href="classes"><i class="la la-chalkboard-teacher"></i> <span>Classes</span></a></li>
                            <li><a href="salas"><i class="la la-door-open"></i> <span>Salas</span></a></li>
                            <li><a href="configuracoes_notas"><i class="la la-calculator"></i> <span>Fórmula de Notas</span></a></li>
                            <li><a href="turnos_horarios"><i class="la la-clock"></i> <span>Horários por Turno</span></a></li>
                            <li><a href="horarios"><i class="la la-calendar-alt"></i> <span>Gestão de horários</span></a></li>
                            <li><a href="precos"><i class="la la-coins"></i> <span>Gestão de Preços</span></a></li>
                            <li><a href="tarefas_administrativas"><i class="la la-tasks"></i> <span>Tarefas Admin</span></a></li>
                            <li><a href="avisos"><i class="la la-bell"></i> <span>Avisos</span></a></li>
                        </ul>
                    </li>

                    <li class="menu-title"><span>Financeiro</span></li>
                    <li class="submenu">
                        <a href="#"><i class="la la-money-bill-wave"></i> <span>Propinas</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="propinas"><i class="la la-id-card"></i> <span>Gestão de Propinas</span></a></li>
                            <li><a href="gerarfatura"><i class="la la-clipboard-check"></i> <span>Gerar Fatura</span></a></li>
                        </ul>
                    </li>
                    <li><a href="pagamentos"><i class="la la-receipt"></i> <span>Pagamentos</span></a></li>

                    <li class="menu-title"><span>Configurações</span></li>
                    <li><a href="permissao"><i class="la la-user-shield"></i> <span>Gestão de Permissões</span></a></li>
                    <li><a href="usuarios"><i class="la la-users-cog"></i> <span>Usuários</span></a></li>
                    <li><a href="configuracoes"><i class="la la-cog"></i> <span>Sistema</span></a></li>
                <?php endif; ?>

                <!-- PROFESSOR -->
                <?php if ($_SESSION['tipo'] === 'Professor') : ?>
                    <li class="menu-title"><span>Principal</span></li>
                    <li><a href="homef"><i class="la la-dashboard"></i> <span>Home</span></a></li>

                    <li class="menu-title"><span>Gestão Acadêmica</span></li>
                    <li><a href="lancar_notas"><i class="la la-id-card"></i> <span>Notas de Frequência</span></a></li>
                    <li><a href="lancar_notas_exame"><i class="la la-file-excel"></i> <span>Notas de Exame</span></a></li>
                    <li><a href="lista_aluno"><i class="la la-clipboard-check"></i> <span>Lista de Alunos</span></a></li>
                    <li><a href="responder_justif"><i class="la la-check-square"></i> <span>Responder a Justificativas</span></a></li>
                    <li><a href="controle_falta"><i class="la la-calendar-times"></i> <span>Faltas dos Alunos</span></a></li>
                <?php endif; ?>

                <!-- SECRETÁRIO -->
                <?php if ($_SESSION['tipo'] === 'Secretário') : ?>
                    <li class="menu-title"><span>Principal</span></li>
                    <li><a href="dashboard_secretario"><i class="la la-dashboard"></i> <span>Dashboard</span></a></li>

                    <li class="menu-title"><span>Gestão Acadêmica</span></li>
                    <li class="submenu">
                        <a href="#"><i class="la la-user-graduate"></i> <span>Alunos</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="lista_aluno"><i class="la la-clipboard-check"></i> <span>Lista de Alunos</span></a></li>
                            <li><a href="matricula"><i class="la la-user-plus"></i> <span>Matrícula</span></a></li>
                            <li><a href="atribuir_turma"><i class="la la-random"></i> <span>Atribuir Turma</span></a></li>
                            <li><a href="controle_falta"><i class="la la-calendar-times"></i> <span>Faltas dos Alunos</span></a></li>
                            <li><a href="justificativas_aprovadas"><i class="la la-check-square"></i> <span>Controle de Justificativas</span></a></li>
                        </ul>
                    </li>
                    <li class="menu-title"><span>Financeiro</span></li>
                    <li class="submenu">
                        <a href="#"><i class="la la-money-bill-wave"></i> <span>Propinas</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="propinas"><i class="la la-id-card"></i> <span>Gestão de Propinas</span></a></li>
                            <li><a href="gerarfatura"><i class="la la-clipboard-check"></i> <span>Gerar Fatura</span></a></li>
                        </ul>
                    </li>
                    <li><a href="pagamentos"><i class="la la-receipt"></i> <span>Pagamentos</span></a></li>

                <?php endif; ?>

                <!-- ALUNO -->
                <?php if ($_SESSION['tipo'] === 'Aluno') : ?>
                    <li class="menu-title"><span>Principal</span></li>
                    <li><a href="homea"><i class="la la-home"></i> <span>Home</span></a></li>

                    <li class="menu-title"><span>Portal do Aluno</span></li>
                    <li><a href="meusdados"><i class="la la-user"></i> <span>Meus Dados</span></a></li>

                    <li class="submenu">
                        <a href="#"><i class="la la-file-alt"></i> <span>Minhas Notas</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="notas_frequencia"><i class="la la-clipboard-list"></i> <span>Notas de Frequência</span></a></li>
                            <?php if ($exibirExames) : ?>
                                <li><a href="notas_exames"><i class="la la-file-excel"></i> <span>Exames</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="la la-wallet"></i> <span>Pagamentos</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="minhas_facturas"><i class="la la-file-invoice-dollar"></i> <span>Minhas Faturas</span></a></li>
                            <li><a href="requisitar_factura"><i class="la la-file-upload"></i> <span>Requisitar Fatura</span></a></li>
                        </ul>
                    </li>
                <?php endif; ?>


                <!-- ALUNO -->
                <?php if ($_SESSION['tipo'] === 'Pedagógico') : ?>
                    <li class="menu-title"><span>Principal</span></li>
                    <li><a href="dashboard"><i class="la la-home"></i> <span>Home</span></a></li>

                    <li class="submenu">
                        <a href="#"><i class="la la-user-graduate"></i> <span>Alunos</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="atribuir_turma"><i class="la la-random"></i> <span>Atribuir Turma</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="la la-chalkboard-teacher"></i> <span>Professores</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="professores"><i class="la la-chalkboard-teacher"></i> <span>Cadastrar Professores</span></a></li>
                            <li><a href="atribuir_turma_classe.php"><i class="la la-tasks"></i> <span>Atribuir T/C</span></a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="la la-cogs"></i> <span>Configurações</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="funcionarios"><i class="la la-users-cog"></i> <span>Funcionários</span></a></li>
                            <li><a href="turmas"><i class="la la-layer-group"></i> <span>Turmas</span></a></li>
                            <li><a href="disciplinas"><i class="la la-book-open"></i> <span>Disciplinas</span></a></li>
                            <li><a href="classes"><i class="la la-chalkboard-teacher"></i> <span>Classes</span></a></li>
                            <li><a href="salas"><i class="la la-door-open"></i> <span>Salas</span></a></li>
                            <li><a href="configuracoes_notas"><i class="la la-calculator"></i> <span>Fórmula de Notas</span></a></li>
                            <li><a href="turnos_horarios"><i class="la la-clock"></i> <span>Horários por Turno</span></a></li>
                            <li><a href="horarios"><i class="la la-calendar-alt"></i> <span>Gestão de horários</span></a></li>
                            <li><a href="precos"><i class="la la-coins"></i> <span>Gestão de Preços</span></a></li>
                            <li><a href="tarefas_administrativas"><i class="la la-tasks"></i> <span>Tarefas Admin</span></a></li>
                            <li><a href="avisos"><i class="la la-bell"></i> <span>Avisos</span></a></li>
                        </ul>
                    </li>
                <?php endif; ?>


            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
