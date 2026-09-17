# GlamTime — Sistema de Agendamento para Salão de Beleza

![Estrutura](img/telaProjeto.png)

Exercício prático de **Web Back-End**: CRUD completo com **PHP 8 + PDO + MySQL + Bootstrap 5.3**, com foco em segurança, regras de negócio reais (conflito de horários) e arquitetura progressiva (DAO, RBAC, transações).

## 🎯 Objetivo pedagógico

Desenvolver as competências de conexão segura a banco de dados, sessões, CRUD parametrizado e camadas de aplicação — preparando o aluno para o padrão usado por frameworks modernos (Laravel/Symfony).

## 🗂️ Estrutura do projeto

```
glamtime/
├── .env                      # Credenciais (fora do Git!)
├── conexao.php               # Conexão PDO central
├── navbar.php / auth_check.php
├── login.php / logout.php    # Autenticação
├── index.php                 # Dashboard
├── agendamentos.php          # Listagem + filtro
├── agendamento_form.php
├── agendamento_salvar.php    # Conflito de horário + transação
├── agendamento_cancelar.php  # Exclusão lógica
├── clientes.php / servicos.php
├── api/agendamentos.php      # Endpoint JSON
├── src/
│   ├── Database.php          # Singleton PDO
│   ├── Agendamento.class.php
│   ├── Cliente.class.php
│   ├── Servico.class.php
│   └── AgendamentoDAO.php    # Camada de acesso a dados
└── sql/glamtime.sql          # DDL + seeds
```

## ⚙️ Setup

1. Clone o repositório: `git clone https://github.com/SU-USUARIO/glamtime.git`
2. Importe o banco: rode `sql/glamtime.sql` no phpMyAdmin ou `mysql -u root < sql/glamtime.sql`
3. Crie o `.env` na raiz (use `.env.example` como base):
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=glamtime
DB_USER=root
DB_PASS=
```
4. Gere as senhas dos usuários e atualize o `sql/glamtime.sql`:
```
php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
```
5. Suba em `public/` ou na raiz do servidor (XAMPP): `localhost/glamtime`
6. Login admin: `admin@glamtime.com` / recepcionista: `recepcao@glamtime.com`

## 🔐 Requisitos técnicos (avaliados)

- Prepared statements em 100% das queries (`EMULATE_PREPARES => false`)
- `password_hash`/`password_verify` + `session_regenerate_id(true)`
- CSRF token em todo POST
- Saída sempre com `htmlspecialchars()`
- RBAC: somente `admin` gerencia serviços
- Bloqueio de conflito de horário antes do INSERT
- `DECIMAL(10,2)` para preços · charset `utf8mb4`

## 📦 Blocos de entrega (commit por bloco)

| Bloco | Escopo | Peso |
|---|---|---|
| 1 | Ambiente, banco, fluxo e sessões | 15% |
| 2 | OO + API JSON | 15% |
| 3 | CRUD + conflito de horário | 25% |
| 4 | Segurança (caça-bugs) | 30% |
| 5 | DAO, Dashboard, transação | 15% |

## 📄 Licença

Material didático — IFSC, uso livre para fins educacionais.
