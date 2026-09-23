<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

if (($_SESSION['usuario_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acesso permitido somente para administradores.');
}

$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
$erro = null;
$sucesso = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['csrf'] ?? '') !== $csrf) {
        $erro = 'Token CSRF inválido.';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $duracao = filter_input(INPUT_POST, 'duracao_min', FILTER_VALIDATE_INT);
        $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);

        if ($nome === '' || $duracao === false || $duracao <= 0 || $preco === false || $preco < 0) {
            $erro = 'Informe nome, duração positiva e preço válido.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO servicos (nome, duracao_min, preco) VALUES (:nome, :duracao, :preco)'
                );
                $stmt->execute([
                    ':nome' => $nome,
                    ':duracao' => $duracao,
                    ':preco' => $preco,
                ]);
                $_SESSION['flash'] = 'Serviço cadastrado com sucesso.';
                header('Location: servicos.php');
                exit;
            } catch (PDOException $e) {
                $erro = $e->getCode() === '23000'
                    ? 'Já existe um serviço com esse nome.'
                    : 'Não foi possível cadastrar o serviço.';
            }
        }
    }
}

$servicos = $pdo->query('SELECT id, nome, duracao_min, preco FROM servicos ORDER BY nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime - Serviços</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container">
    <h4 class="fw-bold mb-3">Serviços</h4>
    <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
    <form method="post" class="card card-body mb-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <div class="row g-2">
        <div class="col-md-5"><input class="form-control" name="nome" placeholder="Nome do serviço" required></div>
        <div class="col-md-3"><input class="form-control" type="number" name="duracao_min" min="1" placeholder="Duração (min)" required></div>
        <div class="col-md-2"><input class="form-control" type="number" name="preco" min="0" step="0.01" placeholder="Preço" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Cadastrar</button></div>
      </div>
    </form>
    <table class="table table-hover bg-white shadow-sm">
      <thead><tr><th>Nome</th><th>Duração</th><th>Preço</th></tr></thead>
      <tbody><?php foreach ($servicos as $servico): ?>
        <tr>
          <td><?= htmlspecialchars($servico['nome']) ?></td>
          <td><?= (int) $servico['duracao_min'] ?> min</td>
          <td>R$ <?= number_format((float) $servico['preco'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
  </main>
</body>
</html>
