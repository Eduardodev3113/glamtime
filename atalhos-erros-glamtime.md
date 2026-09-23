# Atalhos dos erros do GlamTime

Abra cada link, tire o print do trecho indicado e me envie a imagem. A lista segue a mesma numeracao do arquivo [relatorio-erros-glamtime.docs](relatorio-erros-glamtime.docs).

## Erros criticos

1. [Logout vazio](logout.php)
2. [Chamada incorreta de verificarConflito](agendamento_editar.php#L32)
3. [Placeholder SQL reutilizado](src/AgendamentoDAO.php#L64)
4. [var_dump e exit no reagendamento](agentamento_agendar_novamente.php#L17)
5. [Formulario de reagendamento incompleto](agentamento_agendar_novamente.php#L50)
6. [Link para servicos.php inexistente](navbar.php#L14)
7. [Hashes invalidos no SQL](sql/glamtime.sql#L60)

## Falhas de seguranca

8. [API sem autenticacao](api/agendamentos.php#L2)
9. [Salvamento sem autenticacao](agendamento_salvar.php#L2)
10. [Cancelamento sem autenticacao](agendamento_cancelar.php#L2)
11. [Reagendamento sem autenticacao](agentamento_agendar_novamente.php#L2)
12. [Criacao publica de usuario admin](criar_usuario.php#L11)
13. [Token CSRF do registro nao validado](registro.php#L80)
14. [Login sem CSRF](login.php#L47)
15. [Reagendamento sem conferencia do status](agentamento_agendar_novamente.php#L7)

## Regras de negocio

16. [Agendamento no passado](agendamento_salvar.php#L8)
17. [Data invalida na edicao](agendamento_editar.php#L24)
18. [Edicao pode conflitar consigo mesma](src/AgendamentoDAO.php#L57)
19. [IDs de cliente e servico sem validacao](agendamento_salvar.php#L8)
20. [Status alterado sem validacao](agentamento_agendar_novamente.php#L38)

## Configuracao e manutencao

21. [Procedimento incorreto de hashes no README](readme.md)
22. [Reimportacao do SQL pode duplicar dados](sql/glamtime.sql#L65)
23. [Conexao PDO duplicada](conexao.php)
24. [Erro de digitacao no nome do arquivo](agentamento_agendar_novamente.php)

## Como enviar

Envie os prints na ordem dos numeros. Por exemplo:

- `Erro 01 - antes.png`
- `Erro 01 - depois.png`
- `Erro 02 - antes.png`
- `Erro 02 - depois.png`

Pode enviar primeiro apenas os prints do erro 01. Depois que ele for corrigido, envie o print de depois e seguimos para o proximo.
