# Erro 02 - Edicao de agendamento

## Problema

O arquivo `agendamento_editar.php` chama o metodo `verificarConflito()` com tres argumentos, mas o metodo atual em `src/AgendamentoDAO.php` aceita somente dois.

Isso provoca erro fatal ao tentar salvar uma edicao valida.

---

## Antes - `agendamento_editar.php`

```php
if ($novaData === '' || $duracao === 0 || $dao->verificarConflito($novaData, $fim, $id)) {
    $erroDao = 'Horário conflita com outro agendamento ativo.';
} else {
```

### Antes - `src/AgendamentoDAO.php`

```php
public function verificarConflito(string $dataHoraInicio, int $duracaoMin): bool
{
```

A chamada envia:

```text
1. $novaData
2. $fim
3. $id
```

Mas o metodo recebe apenas:

```text
1. $dataHoraInicio
2. $duracaoMin
```

---

## Depois - `agendamento_editar.php`

```php
if (
    $novaData === ''
    || $duracao === 0
    || $dao->verificarConflito($novaData, $duracao, $id)
) {
    $erroDao = 'Horário conflita com outro agendamento ativo.';
} else {
```

## Depois - `src/AgendamentoDAO.php`

```php
public function verificarConflito(
    string $dataHoraInicio,
    int $duracaoMin,
    ?int $ignorarId = null
): bool
{
    $sql = "SELECT COUNT(*) FROM agendamentos a
            JOIN servicos s ON a.servico_id = s.id
            WHERE a.status = 'agendado'
              AND :novo_inicio_1 < DATE_ADD(a.data_hora, INTERVAL s.duracao_min MINUTE)
              AND DATE_ADD(:novo_inicio_2, INTERVAL :duracao MINUTE) > a.data_hora";

    if ($ignorarId !== null) {
        $sql .= " AND a.id <> :ignorar_id";
    }

    $stmt = $this->pdo->prepare($sql);
    $parametros = [
        ':novo_inicio_1' => $dataHoraInicio,
        ':novo_inicio_2' => $dataHoraInicio,
        ':duracao' => $duracaoMin,
    ];

    if ($ignorarId !== null) {
        $parametros[':ignorar_id'] = $ignorarId;
    }

    $stmt->execute($parametros);
    return (int) $stmt->fetchColumn() > 0;
}
```

## O que esta correcao resolve

- Evita o erro de quantidade incorreta de argumentos.
- Permite informar o ID do agendamento que esta sendo editado.
- Impede que o agendamento entre em conflito consigo mesmo.
- Evita reutilizar o mesmo placeholder nomeado na consulta SQL.

## Status

- Antes fotografado: [ ]
- Depois fotografado: [ ]
- Correcao aplicada nos arquivos PHP: [ ]
