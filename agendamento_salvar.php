<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: agendamentos.php'); exit;
}

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$servicoId = (int) ($_POST['servico_id'] ?? 0);
$dataHora  = $_POST['data_hora'] ?? '';

$timestamp = strtotime($dataHora);
if (!$clienteId || !$servicoId || $dataHora === '' || $timestamp === false || $timestamp <= time()) {
    $_SESSION['flash'] = 'Dados inválidos.';
    header('Location: agendamento_form.php'); exit;
}

// Confirma as referências antes de abrir a transação de agendamento.
$validarCliente = $pdo->prepare('SELECT COUNT(*) FROM clientes WHERE id = :id');
$validarCliente->execute([':id' => $clienteId]);
$validarServico = $pdo->prepare('SELECT duracao_min FROM servicos WHERE id = :id');
$validarServico->execute([':id' => $servicoId]);
$duracao = (int) $validarServico->fetchColumn();
if ((int) $validarCliente->fetchColumn() === 0 || $duracao <= 0) {
    $_SESSION['flash'] = 'Cliente ou serviço inválido.';
    header('Location: agendamento_form.php'); exit;
}
$fim = date('Y-m-d H:i:s', $timestamp + $duracao * 60);

// Regra de negócio: bloqueio de conflito de horário (sobreposição de intervalos)
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos a JOIN servicos s ON a.servico_id = s.id
        WHERE a.status = 'agendado'
                    AND :novo_inicio < DATE_ADD(a.data_hora, INTERVAL s.duracao_min MINUTE)
                    AND :novo_fim > a.data_hora");
    $stmt->execute([
                ':novo_inicio' => $dataHora,
                ':novo_fim' => $fim,
    ]);

    if ((int) $stmt->fetchColumn() > 0) {
        throw new RuntimeException('Horário conflita com outro agendamento ativo.');
    }

    $ins = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status) VALUES (:c, :s, :d, 'agendado')");
    $ins->execute([':c' => $clienteId, ':s' => $servicoId, ':d' => $dataHora]);
    $pdo->commit();
    $_SESSION['flash'] = 'Agendamento criado com sucesso!';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = $e->getMessage();
}
header('Location: agendamentos.php');
exit;