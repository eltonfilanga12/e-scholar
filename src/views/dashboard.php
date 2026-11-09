<?php
require_once __DIR__ . '/../models/DashboardModel.php';

$dashboard = new DashboardModel();

$totalAlunos = $dashboard->getTotalAlunos();
$totalProfessores = $dashboard->getTotalProfessores();
$totalDisciplinas = $dashboard->getTotalDisciplinas();
$totalFuncionarios = $dashboard->getTotalFuncionarios();
$logs = $dashboard->listarLogs();
$estatisticasPresencas = $dashboard->getEstatisticasPresencas();
$totalSalas = $dashboard->getTotalSalas();
$novasMatriculas = $dashboard->getTotalAlunos(); // Exemplo simplificado
$totalPropinas = $dashboard->getPropinasArrecadadasPorMes(date('Y')); // total mensal
$propinasEmAtraso = $dashboard->getPropinasEmAtraso();
$avisos = $dashboard->getAvisos();
$tarefas = $dashboard->getTarefasAdministrativas();


$anoAtual = date('Y');

$propinasMes = $dashboard->getPropinasArrecadadasPorMes($anoAtual);
$frequenciaMes = $dashboard->getFrequenciaAlunosPorMes($anoAtual);

// Organiza os dados para gráfico (labels e dados)

$meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

$propinasData = array_fill(0, 12, 0);
foreach ($propinasMes as $p) {
	$index = array_search($p['mes'], $meses);
	if ($index !== false) {
		$propinasData[$index] = (float)$p['total_arrecadado'];
	}
}

$frequenciaData = array_fill(0, 12, 0);
foreach ($frequenciaMes as $f) {
	$mesIndex = $f['mes_num'] - 1;
	if (isset($frequenciaData[$mesIndex])) {
		$frequenciaData[$mesIndex] = (int)$f['presencas'];
	}
}


require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">

	<!-- Page Content -->
	<div class="content container-fluid">

		<!-- Page Header -->
		<div class="page-header">
			<div class="row">
				<div class="col-sm-12">
					<h3 class="page-title">Bem-vindo, Administrador!</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item active">Painel de Controle</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-6 col-lg-3">
				<div class="card dash-widget">
					<div class="card-body">
						<span class="dash-widget-icon"><i class="la la-user-graduate"></i></span>
						<div class="dash-widget-info">
							<h3><?= $totalAlunos ?></h3>
							<span>Alunos</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card dash-widget">
					<div class="card-body">
						<span class="dash-widget-icon"><i class="la la-chalkboard-teacher"></i></span>
						<div class="dash-widget-info">
							<h3><?= $totalProfessores ?></h3>
							<span>Professores</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card dash-widget">
					<div class="card-body">
						<span class="dash-widget-icon"><i class="la la-book-open"></i></span>
						<div class="dash-widget-info">
							<h3><?= $totalDisciplinas ?></h3>
							<span>Disciplinas</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-lg-3">
				<div class="card dash-widget">
					<div class="card-body">
						<span class="dash-widget-icon"><i class="la la-users"></i></span>
						<div class="dash-widget-info">
							<h3><?= $totalFuncionarios ?></h3>
							<span>Funcionários</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Relatórios -->
		<div class="row">
			<div class="col-md-6 text-center">
				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Propinas Arrecadadas</h3>
						<div id="bar-charts"></div>
					</div>
				</div>
			</div>
			<div class="col-md-6 text-center">
				<div class="card">
					<div class="card-body">
						<h3 class="card-title">Frequência de Alunos</h3>
						<div id="line-charts"></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Estatísticas -->
		<div class="row">
			<div class="col-md-3">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Novas Matrículas</h5>
						<h3 class="mb-3"><?= $novasMatriculas ?></h3>
						<div class="progress mb-2">
							<div class="progress-bar bg-success" style="width: 75%"></div>
						</div>
						<p>Comparado ao mês anterior</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Total de Propinas</h5>
						<h3 class="mb-3">MZN <?= number_format(array_sum(array_column($totalPropinas, 'total_arrecadado')), 2, ',', '.') ?></h3>
						<div class="progress mb-2">
							<div class="progress-bar bg-primary" style="width: 80%"></div>
						</div>
						<p>Mês Atual</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Propinas em Atraso</h5>
						<h3 class="mb-3">MZN <?= number_format($propinasEmAtraso, 2, ',', '.') ?></h3>
						<div class="progress mb-2">
							<div class="progress-bar bg-warning" style="width: 40%"></div>
						</div>
						<p>Em cobrança</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Salas de Aula</h5>
						<h3 class="mb-3"><?= $totalSalas ?></h3>
						<div class="progress mb-2">
							<div class="progress-bar bg-info" style="width: 100%"></div>
						</div>
						<p>Ocupação Total</p>
					</div>
				</div>
			</div>
		</div>

		<!-- NOVAS SEÇÕES -->

		<!-- Estatísticas de Presenças -->
		<div class="row mt-4">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h5>Estatísticas de Presenças</h5>
					</div>
					<div class="card-body">
						<div class="row text-center">
							<div class="col-md-4">
								<h3><?= $estatisticasPresencas['total_aulas'] ?? 0 ?></h3>
								<p>Total de Aulas</p>
							</div>
							<div class="col-md-4">
								<h3><?= $estatisticasPresencas['presencas'] ?? 0 ?></h3>
								<p>Presenças Registradas</p>
							</div>
							<div class="col-md-4">
								<h3><?= $estatisticasPresencas['faltas'] ?? 0 ?></h3>
								<p>Faltas Registradas</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<!-- Avisos -->
		<!-- Avisos -->
		<div class="row mt-4">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h5>Avisos Ativos</h5>
					</div>
					<div class="card-body">
						<ul class="list-group">
							<?php if (!empty($avisos)): ?>
								<?php
								$temAtivo = false;
								foreach ($avisos as $aviso):
									if ($aviso['status'] === 'Ativo'):
										$temAtivo = true;
								?>
										<li class="list-group-item">
											<?= htmlspecialchars($aviso['conteudo']) ?>
											<br>
											<small class="text-muted">
												Validade: <?= $aviso['data_validade'] ? date('d/m/Y', strtotime($aviso['data_validade'])) : 'Sem data' ?>
											</small>
										</li>
								<?php
									endif;
								endforeach;
								?>
								<?php if (!$temAtivo): ?>
									<li class="list-group-item text-muted">Nenhum aviso ativo.</li>
								<?php endif; ?>
							<?php else: ?>
								<li class="list-group-item text-muted">Nenhum aviso disponível.</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>


		<!-- Tarefas Administrativas -->
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4>Tarefas Administrativas</h4>
					</div>
					<div class="card-body table-responsive">
						<table class="display table table-striped table-hover">
							<thead>
								<tr>
									<th>Tarefa</th>
									<th>Responsável</th>
									<th>Status</th>
									<th>Prazo</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($tarefas)): ?>
									<?php foreach ($tarefas as $tarefa): ?>
										<tr>
											<td><?= htmlspecialchars($tarefa['titulo']) ?></td>
											<td><?= htmlspecialchars($tarefa['responsavel']) ?></td>
											<td>
												<?php
												$badgeClass = match ($tarefa['status']) {
													'Concluído' => 'success',
													'Pendente' => 'warning',
													'Atrasado' => 'danger',
													default => 'secondary',
												};
												?>
												<span class="badge bg-<?= $badgeClass ?>"><?= $tarefa['status'] ?></span>
											</td>
											<td><?= date('d/m/Y', strtotime($tarefa['prazo'])) ?></td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr>
										<td colspan="4" class="text-center text-muted">Nenhuma tarefa cadastrada.</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Logs de Atividades -->


		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4>Logs de Atividades</h4>
					</div>
					<div class="card-body table-responsive">
						<table id="basic-datatables" class="display table table-striped table-hover">
							<thead>
								<tr>
									<th>Data/Hora</th>
									<th>Usuário</th>
									<th>Ação</th>
									<th>Descrição</th>
									<th>IP</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($logs as $log): ?>
									<tr>
										<td><?= htmlspecialchars($log['data_hora']) ?></td>
										<td><?= htmlspecialchars($log['usuario']) ?></td>
										<td><?= htmlspecialchars($log['acao']) ?></td>
										<td><?= htmlspecialchars($log['descricao']) ?></td>
										<td><?= htmlspecialchars($log['ip']) ?></td>

									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>




	</div>
	<!-- /Page Content -->
</div>
<!-- /Page Wrapper -->



<?php
require_once __DIR__ . '/partials/footer.php';
?>

<script>

	const ctxBar = document.getElementById('bar-charts').getContext('2d');
	const ctxLine = document.getElementById('line-charts').getContext('2d');

	const meses = <?= json_encode($meses) ?>;
	const propinasData = <?= json_encode($propinasData) ?>;
	const frequenciaData = <?= json_encode($frequenciaData) ?>;

	const barChart = new Chart(ctxBar, {
		type: 'bar',
		data: {
			labels: meses,
			datasets: [{
				label: 'Propinas Arrecadadas (MZN)',
				data: propinasData,
				backgroundColor: 'rgba(54, 162, 235, 0.6)',
				borderColor: 'rgba(54, 162, 235, 1)',
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});

	const lineChart = new Chart(ctxLine, {
		type: 'line',
		data: {
			labels: meses,
			datasets: [{
				label: 'Frequência de Presenças',
				data: frequenciaData,
				fill: false,
				borderColor: 'rgba(255, 99, 132, 1)',
				tension: 0.1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});
</script>