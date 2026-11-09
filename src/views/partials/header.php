<?php
session_start();

if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

require_once __DIR__ . '/../../../config/database.php';

$instituicaoNome = "Instituição Desconhecida";

if (isset($_SESSION['instituicao_id'])) {
	$db = new Database();
	$conn = $db->getConnection();

	$stmt = $conn->prepare("SELECT nome FROM instituicoes WHERE id_instituicoes = :id");
	$stmt->bindParam(':id', $_SESSION['instituicao_id']);
	$stmt->execute();
	$instituicao = $stmt->fetch();

	if ($instituicao) {
		$instituicaoNome = $instituicao['nome'];
	}
}
?>


<!-- Header -->
<div class="header">

	<!-- Logo -->
	<div class="header-left">
		<a href="#" class="logo">
			<img id="logo-img" src="../../public/img/logoSchol.png" height="50" alt="logoSys">
		</a>
	</div>
	<!-- /Logo -->

	<a id="toggle_btn" href="javascript:void(0);">
		<span class="bar-icon">
			<span></span>
			<span></span>
			<span></span>
		</span>
	</a>

	<!-- Header Title -->
	<div class="page-title-box">
		<h3><?php echo htmlspecialchars($instituicaoNome); ?></h3>
	</div>
	<!-- /Header Title -->

	<a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>

	<!-- Header Menu -->
	<ul class="nav user-menu">

		<!-- Search -->
		<!-- /Search -->

		<!-- /Flag -->

		<li class="nav-item dropdown has-arrow main-drop">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<i class="fa fa-user"></i>
				<span><?php echo $_SESSION['nome']; ?></span>
			</a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="mudar_senha.php">Mudar senha</a>
				<a class="dropdown-item" href="logout.php">Logout</a>
			</div>
		</li>
	</ul>
	<!-- /Header Menu -->

	<!-- Mobile Menu -->
	<div class="dropdown mobile-user-menu">
		<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
		<div class="dropdown-menu dropdown-menu-right">
			<a class="dropdown-item" href="#">Meu perfil</a>
			<a class="dropdown-item" href="login.html">Logout</a>
		</div>
	</div>
	<!-- /Mobile Menu -->

</div>
<!-- /Header -->