<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/src/AgendamentoDAO.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT a.cliente_id, a.servico_id, c.nome cliente, s.nome servico, s.duracao_min
     FROM agendamentos a
     JOIN clientes c ON c.id = a.cliente_id
     JOIN servicos s ON s.id = a.servico_id
     WHERE a.id = :id AND a.status = 'agendado'"
);
$stmt->execute([':id' => $id]);
$ag = $stmt->fetch();
if (!$ag) {
    header('Location: agendamentos.php');
    exit;
}

$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
$erro = null;
$novaData = $_POST['data_hora'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['csrf'] ?? '') !== $csrf) {
        $erro = 'Token CSRF inválido.';
    } else {
        $timestamp = strtotime($novaData);
        if ($timestamp === false || $timestamp <= time()) {
            $erro = 'Informe uma data futura válida.';
        } else {
            $dao = new AgendamentoDAO($pdo);
            $pdo->beginTransaction();
            try {
                if ($dao->verificarConflito($novaData, (int) $ag['duracao_min'])) {
                    throw new RuntimeException('Horário conflita com outro agendamento.');
                }

                $ins = $pdo->prepare(
                    "INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status)
                     VALUES (:cliente, :servico, :data, 'agendado')"
                );
                $ins->execute([
                    ':cliente' => (int) $ag['cliente_id'],
                    ':servico' => (int) $ag['servico_id'],
                    ':data' => date('Y-m-d H:i:s', $timestamp),
                ]);

                $upd = $pdo->prepare(
                    "UPDATE agendamentos SET status = 'concluido'
                     WHERE id = :id AND status = 'agendado'"
                );
                $upd->execute([':id' => $id]);
                if ($upd->rowCount() !== 1) {
                    throw new RuntimeException('O agendamento original já foi processado.');
                }

                $pdo->commit();
                $_SESSION['flash'] = 'Novo agendamento criado a partir do anterior.';
                header('Location: agendamentos.php');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erro = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Agendar novamente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container" style="max-width:560px">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="fw-bold mb-3">Agendar novamente</h5>
      <p>Cliente: <?= htmlspecialchars($ag['cliente']) ?><br>Serviço: <?= htmlspecialchars($ag['servico']) ?></p>
      <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label class="form-label" for="data_hora">Nova data e hora</label>
        <input class="form-control mb-3" id="data_hora" type="datetime-local" name="data_hora" value="<?= htmlspecialchars($novaData) ?>" required>
        <button class="btn btn-success w-100 mb-2" type="submit">Confirmar reagendamento</button>
        <a class="btn btn-link w-100" href="agendamentos.php">Cancelar</a>
      </form>
    </div></div>
  </main>
</body>
</html>