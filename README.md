# Helpdesk — Sistema de Chamados

[![CI](https://github.com/Lukaasz01/helpdesk-app/actions/workflows/ci.yml/badge.svg)](https://github.com/Lukaasz01/helpdesk-app/actions/workflows/ci.yml)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Testes](https://img.shields.io/badge/testes-61%20passando-success)](tests)
[![Licença MIT](https://img.shields.io/badge/licen%C3%A7a-MIT-blue)](LICENSE)

Sistema de abertura e acompanhamento de chamados técnicos em **Laravel 12**, com controle de acesso por papel: quem abre o chamado, quem atende e quem administra veem coisas diferentes.

## O problema

Suporte técnico organizado por e-mail se perde: não há número de protocolo, ninguém sabe quem ficou responsável e o cliente não consegue acompanhar o andamento. Aqui cada chamado tem **código único** (`OS-2026-0001`), **responsável**, **status** e **prioridade**, com histórico de comentários no próprio chamado.

## Rodando em um comando

```bash
git clone https://github.com/Lukaasz01/helpdesk-app.git
cd helpdesk-app
docker compose up -d
```

Acesse **http://localhost:8080**. O contêiner sobe a aplicação, espera o MySQL ficar pronto e roda as migrações sozinho — não é preciso ter PHP, Composer, Node ou MySQL instalados.

Para popular com dados de demonstração:

```bash
docker compose exec app php artisan migrate:fresh --seed --seeder=DemoSeeder
```

### Usuários de demonstração

| Papel | E-mail | Senha |
|---|---|---|
| Administrador | `admin@helpdesk.test` | `password` |
| Técnico | `tecnico@helpdesk.test` | `password` |
| Cliente | `cliente@helpdesk.test` | `password` |

Entre com cada um para ver como a mesma tela muda conforme o papel.

## Funcionalidades

- **Chamados (CRUD)** — abertura, consulta, atualização e encerramento
- **Protocolo único** — código sequencial por ano, gerado sob transação com *lock*
- **Ciclo de vida** — `aberto` → `em andamento` → `resolvido` → `fechado`
- **Prioridades** — baixa, média, alta e urgente
- **Papéis** — cliente (abre), técnico (atende) e administrador (enxerga tudo)
- **Autorização por registro** — cada chamado é verificado individualmente
- **Comentários** — histórico de interação dentro do chamado
- **Busca e filtros** — por código, título, status e prioridade
- **Dashboard** — visão consolidada respeitando o papel do usuário
- **Autenticação completa** — cadastro, login, verificação de e-mail e recuperação de senha

## Segurança e autorização

A listagem sempre filtrou os chamados por papel, mas isso **não protegia o registro**: bastava trocar o id na URL para ler ou alterar o chamado de outra pessoa. A `TicketPolicy` fecha esse caminho, e a suíte de testes cobre exatamente esse acesso direto.

| Ação | Cliente | Técnico | Administrador |
|---|:---:|:---:|:---:|
| Ver o próprio chamado | ✅ | — | ✅ |
| Ver chamado atribuído a si | — | ✅ | ✅ |
| Ver chamado de terceiros | ❌ | ❌ | ✅ |
| Abrir chamado | ✅ | ✅ | ✅ |
| Alterar status / atribuir técnico | ❌ | ✅ (só o seu) | ✅ |
| Comentar | ✅ (no seu) | ✅ (no seu) | ✅ |

O campo `technician_id` só aceita usuários que realmente têm o papel de técnico — `exists:users,id` sozinho permitiria atribuir um chamado a um cliente.

## Testes

```bash
php artisan test
```

**61 testes / 135 asserções**, em SQLite na memória — não precisam de banco externo.

| Arquivo | Cobre |
|---|---|
| `TicketAuthorizationTest` | Acesso direto por URL, isolamento entre clientes e entre técnicos, privilégio do admin |
| `TicketTest` | CRUD, filtros, busca, ciclo de vida e data de resolução |
| `TicketCodeTest` | Formato, sequência e unicidade do protocolo |
| `CommentTest` | Quem pode comentar e validação do conteúdo |
| `DashboardTest` | Visibilidade do painel por papel |
| `DemoSeederTest` | Consistência e idempotência dos dados de demonstração |
| `Auth/*`, `ProfileTest` | Autenticação, cadastro, senha e perfil |

## Integração contínua

O workflow em `.github/workflows/ci.yml` roda a cada push e pull request:

| Job | O que faz |
|---|---|
| **Testes** | Suíte completa em PHP 8.2 e 8.3 |
| **Padrão de código** | `pint --test`, sem alterar arquivos |
| **Docker** | Constrói a imagem, sobe o contêiner e confirma que `/up` responde |

O job de Docker é o que garante que a instrução de instalação do README continua funcionando — um build quebrado falha o CI em vez de ser descoberto por quem clonou.

## Stack

PHP 8.2+ · Laravel 12 · [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) · Blade · Bootstrap · MySQL / PostgreSQL / SQLite · Pest 3 · Laravel Pint · Docker · Nginx · GitHub Actions

## Arquitetura

```
app/
  Http/Controllers/     TicketController, CommentController, DashboardController
  Http/Requests/        validação isolada do controller (StoreTicketRequest)
  Policies/             TicketPolicy — autorização por registro
  Models/               Ticket, Comment, User
database/
  migrations/           esquema versionado
  factories/            geração de dados para os testes
  seeders/DemoSeeder    base de demonstração
docker/
  nginx/                virtual host com raiz em public/
  php/                  opcache, supervisor e entrypoint
tests/Feature/          suíte Pest
.github/workflows/      pipeline de CI
```

### Modelagem

```
User  1 ──── N  Ticket   (client_id — quem abriu)
User  1 ──── N  Ticket   (technician_id — quem atende)
Ticket 1 ──── N  Comment
User  N ──── N  Role/Permission
```

`Ticket` referencia `users` duas vezes com papéis distintos. A exclusão em cascata vale só para o cliente; remover um técnico apenas libera o campo (`nullOnDelete`), porque o chamado precisa sobreviver à saída de quem o atendia.

## Instalação sem Docker

**Pré-requisitos:** PHP 8.2+, Composer, Node.js e um banco (MySQL, PostgreSQL ou SQLite).

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

php artisan migrate --seed --seeder=DemoSeeder
php artisan serve
```

## Deploy

A imagem Docker é autossuficiente e serve para qualquer plataforma que aceite contêiner. O repositório traz um [blueprint do Render](render.yaml): em *New → Blueprint*, apontando para este repositório, ele cria a aplicação e o banco PostgreSQL já conectados, gerando a `APP_KEY` automaticamente.

Variáveis necessárias em produção:

| Variável | Observação |
|---|---|
| `APP_KEY` | Gere com `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `mysql`, `pgsql` ou `sqlite` |
| `DB_HOST` `DB_PORT` `DB_DATABASE` `DB_USERNAME` `DB_PASSWORD` | Conexão do banco |

O `entrypoint` roda as migrações e monta os caches de configuração, rotas e views a cada início do contêiner. O endpoint de saúde fica em `/up`.

> Planos gratuitos de qualquer provedor mudam com frequência — confirme os limites atuais antes de escolher.

## Decisões técnicas

- **`spatie/laravel-permission` em vez de um campo `role`** — criar um papel novo ou ajustar permissões não exige alterar código nem migrar dados.
- **Policy em vez de checagem no controller** — a regra fica em um lugar só e vale para qualquer caminho que chegue ao chamado, inclusive rotas futuras.
- **Protocolo sequencial sob transação com `lockForUpdate`** — a versão anterior usava 4 caracteres de `uniqid()`: com cerca de 300 chamados a chance de repetir passa de 50%, e a coluna é `UNIQUE`, o que quebraria a abertura com erro de banco.
- **Status e prioridade como `enum` no banco** — o banco recusa valor inválido mesmo que a aplicação erre.
- **Cast de `resolved_at` para `datetime`** — sem ele o campo volta como string e qualquer cálculo de data quebra.
- **Testes em SQLite na memória** — a suíte roda em segundos, no CI e na máquina de quem clonou, sem depender de serviço externo.
- **Imagem Docker multi-estágio** — Composer e Node ficam fora da imagem final, que carrega apenas o runtime.
- **`.gitattributes` com `eol=lf`** — clonando no Windows, o `entrypoint.sh` chegaria com CRLF e o contêiner falharia ao iniciar.

## Melhorias mapeadas

- [ ] Notificação por e-mail a cada mudança de status
- [ ] Métricas de tempo médio de atendimento no dashboard
- [ ] Anexos nos chamados
- [ ] Exportação de relatórios em CSV
- [ ] Testes de browser (Dusk) para os fluxos principais

## Licença

[MIT](LICENSE)
