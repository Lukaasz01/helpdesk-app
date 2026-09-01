# Helpdesk — Sistema de Chamados

Sistema de abertura e acompanhamento de chamados técnicos construído em **Laravel 12**, com controle de acesso por papel: quem abre o chamado, quem atende e quem administra veem coisas diferentes.

## O problema

Suporte técnico organizado por e-mail se perde: não há número de protocolo, ninguém sabe quem ficou responsável, e o cliente não consegue acompanhar o andamento. Este sistema dá a cada chamado um **código único** (`OS-2026-0001`), um **responsável**, um **status** e uma **prioridade**, com histórico de comentários no próprio chamado.

## Funcionalidades

- **Chamados (CRUD completo)** — abertura, consulta, edição e encerramento
- **Código de protocolo único** — gerado no padrão `OS-ANO-NNNN`, com unicidade garantida no banco
- **Ciclo de vida do chamado** — `aberto` → `em andamento` → `resolvido` → `fechado`
- **Prioridades** — baixa, média, alta e urgente
- **Papéis distintos** — cliente (abre), técnico (atende) e administrador
- **Comentários** — histórico de interação dentro de cada chamado
- **Dashboard** — visão consolidada dos chamados
- **Autenticação completa** — cadastro, login, verificação de e-mail, recuperação e troca de senha
- **Perfil do usuário** — edição de dados e exclusão da conta

## Modelagem

```
User  1 ──── N  Ticket   (como client_id — quem abriu)
User  1 ──── N  Ticket   (como technician_id — quem atende)
Ticket 1 ──── N  Comment
User  N ──── N  Role/Permission   (spatie/laravel-permission)
```

O `Ticket` referencia `users` **duas vezes**, com papéis diferentes na mesma tabela. A exclusão em cascata vale só para o cliente (`onDelete('cascade')`); remover um técnico apenas libera o campo (`nullOnDelete`), porque o chamado precisa sobreviver à saída de quem o atendia.

## Stack

PHP 8.2 · Laravel 12 · [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) · Blade · Bootstrap · MySQL · Eloquent ORM

## Rotas

| Método | Rota | Descrição | Acesso |
|---|---|---|---|
| `GET` | `/dashboard` | Painel com os chamados | autenticado + verificado |
| `GET·POST·PUT·DELETE` | `/tickets` | CRUD de chamados (`Route::resource`) | autenticado + verificado |
| `POST` | `/tickets/{ticket}/comments` | Comenta em um chamado | autenticado + verificado |
| `GET·PATCH·DELETE` | `/profile` | Gerencia o próprio perfil | autenticado |

## Como rodar

**Pré-requisitos:** PHP 8.2+, Composer, MySQL e Node.js.

```bash
git clone https://github.com/Lukaasz01/helpdesk-app.git
cd helpdesk-app

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
# configure DB_DATABASE, DB_USERNAME e DB_PASSWORD no .env

php artisan migrate
php artisan serve
```

Disponível em `http://localhost:8000`.

## Decisões técnicas

- **`spatie/laravel-permission` em vez de um campo `role`** — permissões ficam em tabelas próprias, então criar um papel novo ou ajustar o que cada um pode fazer não exige alterar código nem migrar dados.
- **Status e prioridade como `enum` no banco** — o banco recusa um valor inválido mesmo que a aplicação erre, em vez de confiar apenas na validação da camada PHP.
- **`code` com índice único** — o protocolo é o identificador que o usuário enxerga e comunica; a unicidade é garantida no banco, não só na aplicação.
- **Form Requests (`StoreTicketRequest`)** — a validação fica fora do controller, que só orquestra.
- **Comentários ordenados por `latest()` na relação** — a ordenação é regra do modelo, não responsabilidade de cada consulta.
- **Middleware `verified` nas rotas de chamado** — só quem confirmou o e-mail abre chamado, o que reduz abertura por conta falsa.

## Melhorias mapeadas

- [ ] Testes de feature cobrindo as permissões por papel
- [ ] Notificação por e-mail a cada mudança de status
- [ ] Filtros e busca por status, prioridade e técnico
- [ ] Métricas de tempo de atendimento no dashboard
- [ ] Anexos nos chamados

## Licença

[MIT](LICENSE)
