# Mail Monitor API (Laravel 12)

API para ingestão e consulta de eventos Exim/Dovecot, com autenticação via cPanel e documentação OpenAPI.

## Requisitos

- Docker + Docker Compose
- Laravel Sail

## Instalação

```bash
cp .env.example .env
vendor/bin/sail up -d
vendor/bin/sail composer install
vendor/bin/sail artisan key:generate
```

## Configuração (.env)

Banco PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=mail_monitor
DB_USERNAME=sail
DB_PASSWORD=password
```

Defaults e integração cPanel:

```env
API_VERSION=1.0.0
EMAIL_MONITOR_DEFAULT_WHM_ACCOUNT=rmlad479
EMAIL_MONITOR_DEFAULT_DOMAIN=rmladv.com.br
CPANEL_BASE_URL=https://SEU_HOST:2087
CPANEL_USERNAME=
CPANEL_API_TOKEN=
CPANEL_VERIFY_SSL=true
```

Ingestão e Swagger:

```env
PYTHON_INGEST_SHARED_SECRET=uma-chave-forte
SWAGGER_ENABLED=true
```

## Migrations

```bash
vendor/bin/sail artisan migrate
```

## Swagger / OpenAPI

- UI: `GET /api/docs`
- JSON: `GET /api/openapi.json`

> Em ambientes não locais, habilite `SWAGGER_ENABLED=true` para acessar a documentação.

Gerar/atualizar o spec:

```bash
vendor/bin/sail artisan l5-swagger:generate
```

Opcionalmente, defina `L5_SWAGGER_GENERATE_ALWAYS=true` no `.env` para gerar automaticamente.

## Painel Admin (FilamentPHP v5)

### Instalação do Painel

As dependências já estão incluídas no `composer.json`. Após instalar, execute:

```bash
# Migrations (cria tabelas de roles/permissions e last_seen_at nos ingest_clients)
vendor/bin/sail artisan migrate

# Seeders (roles, permissions e usuário admin padrão)
vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
vendor/bin/sail artisan db:seed --class=AdminUserSeeder

# Compilar assets do Filament
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

### Variáveis de ambiente do admin

```env
ADMIN_EMAIL=admin@mailmonitor.local
ADMIN_PASSWORD=password
ADMIN_NAME=Administrador
```

### Acesso

Acesse `/admin` no navegador. Credenciais padrão:

- **E-mail**: `admin@mailmonitor.local` (ou valor de `ADMIN_EMAIL`)
- **Senha**: `password` (ou valor de `ADMIN_PASSWORD`)

> Troque a senha após o primeiro acesso.

### Roles e Permissões

| Role | Permissões |
|------|-----------|
| `admin` | Tudo — resources, usuários, métricas |
| `operator` | viewer + gerenciar Mailboxes, cPanel Accounts, Ingest Clients |
| `viewer` | Apenas leitura — Mail Events, Auth Events, métricas |

Para criar novos usuários com roles, acesse **Administração → Usuários** no painel.

### Estrutura do Painel

| Grupo | Resource | Roles com acesso |
|-------|----------|-----------------|
| Monitoramento | Mail Events | viewer, operator, admin |
| Monitoramento | Auth Events | viewer, operator, admin |
| Configuração | Mailboxes | operator, admin |
| Configuração | Contas cPanel | operator, admin |
| Configuração | Clientes Ingest | operator, admin |
| Administração | Usuários | admin |

### Actions especiais

- **Testar Credenciais** (Contas cPanel): valida o token cPanel atual e atualiza `token_last_verified_at`.
- **Sincronizar Mailboxes** (Contas cPanel): importa mailboxes do cPanel para a tabela `mailboxes`.
- **Gerar Novo Segredo** (Clientes Ingest): gera um novo `shared_secret` e exibe **apenas uma vez** via notificação.

### Comandos artisan usados

```bash
vendor/bin/sail composer require filament/filament:"^5.0" spatie/laravel-permission
vendor/bin/sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
vendor/bin/sail artisan filament:install --scaffold
vendor/bin/sail artisan migrate
vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
vendor/bin/sail artisan db:seed --class=AdminUserSeeder
```

---

## Fluxo de cadastro via cPanel

1. O frontend chama `POST /api/v1/auth/register` com nome, e-mail, senha, domínio, WHM account, host do cPanel e token.
2. A API valida o token do cPanel.
3. Se válido, cria o usuário e associa o `cpanel_account` com token criptografado.
4. Retorna token Sanctum e perfil do usuário.

## Exemplos cURL

Registro:

```bash
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin",
    "email": "admin@rmladv.com.br",
    "password": "secret123",
    "whm_account": "rmlad479",
    "domain": "rmladv.com.br",
    "cpanel_host": "https://SEU_HOST:2087",
    "cpanel_api_token": "TOKEN_AQUI",
    "cpanel_username": "cpanel_user"
  }'
```

Login:

```bash
curl -X POST http://localhost/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@rmladv.com.br",
    "password": "secret123"
  }'
```

Ingestão:

```bash
curl -X POST http://localhost/api/v1/ingest/events \
  -H "Content-Type: application/json" \
  -H "X-Ingest-Secret: uma-chave-forte" \
  -d '{
    "source": "python-agent",
    "domain": "rmladv.com.br",
    "whm_account": "rmlad479",
    "mail_events": [
      {
        "occurred_at": "2026-02-27T10:30:00-03:00",
        "direction": "inbound",
        "event_type": "mail_in_accepted",
        "exim_message_id": "1abcXYZ",
        "sender": "alice@example.com",
        "recipient": "bob@rmladv.com.br",
        "status": "accepted"
      }
    ],
    "auth_events": [
      {
        "occurred_at": "2026-02-27T10:32:00-03:00",
        "proto": "imap",
        "event_type": "login_success",
        "user_email": "bob@rmladv.com.br",
        "auth_result": "success"
      }
    ]
  }'
```

Consulta de eventos:

```bash
curl -X GET "http://localhost/api/v1/mail/events?start_at=2026-02-27T00:00:00-03:00&end_at=2026-02-27T23:59:59-03:00" \
  -H "Authorization: Bearer SEU_TOKEN"
```

Métricas:

```bash
curl -X GET "http://localhost/api/v1/metrics/overview?start_at=2026-02-20T00:00:00-03:00&end_at=2026-02-27T23:59:59-03:00" \
  -H "Authorization: Bearer SEU_TOKEN"
```

## Seeders (opcional)

```bash
vendor/bin/sail artisan db:seed
```

## Observações

- Datas devem estar em ISO8601.
- Paginação padrão: 50 itens (máximo 200).
- As rotas `/api/v1/*` (exceto health e auth) usam `auth:sanctum`.
- Caso a UI não reflita mudanças, rode `vendor/bin/sail npm run dev`.
