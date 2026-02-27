Você é um desenvolvedor Laravel sênior. Crie um MVP de uma API Laravel (Laravel 12) para um dashboard de monitoramento de e-mail (Exim/Dovecot) hospedado em WHM/cPanel. O frontend (Vue) consumirá JSON.

Arquitetura geral:
- Um serviço Python (rodando no servidor WHM/cPanel) fará o parsing dos logs (Exim/Dovecot) e enviará eventos normalizados para esta API Laravel.
- A API Laravel armazenará os eventos em PostgreSQL e exporá endpoints de consulta/ métricas para o dashboard.
- O cadastro/autorização de usuários deve integrar com cPanel via API (UAPI/WHM API), guardando token e dados necessários no PostgreSQL.
- A documentação da API deve ser gerada via Swagger/OpenAPI.

Requisitos técnicos:
1) Banco: PostgreSQL (usar migrations e tipos adequados: jsonb, indexes, enums).
2) Autenticação: Laravel Sanctum (token bearer). Todas as rotas /api/v1/* protegidas por auth:sanctum, exceto /health e rotas de auth necessárias.
3) Versionamento: /api/v1/...
4) Datas em ISO8601, timezone configurável (APP_TIMEZONE) e filtros start_at/end_at.
5) Validação via FormRequest; saída via API Resources; Controllers finos chamando Services.

Configuração (.env):
- EMAIL_MONITOR_DEFAULT_WHM_ACCOUNT=rmlad479
- EMAIL_MONITOR_DEFAULT_DOMAIN=rmladv.com.br
- CPANEL_BASE_URL=https://SEU_HOST:2087 (ou 2083, conforme estratégia)
- CPANEL_USERNAME= (opcional, se usar integração baseada em credenciais do sistema)
- CPANEL_API_TOKEN= (token de API para chamadas server-side)
- CPANEL_VERIFY_SSL=true/false
- PYTHON_INGEST_SHARED_SECRET=uma-chave-forte (para autenticar o serviço Python)
- SWAGGER_ENABLED=true

Domínio de dados (tabelas / models / migrations):
A) mail_events
- id bigserial
- occurred_at timestamptz indexed
- direction enum: inbound|outbound|local
- event_type text indexed (ex: mail_in_accepted, mail_in_rejected, mail_out_sent, mail_out_deferred, mail_bounced, mail_delivered_local, queue_depth)
- exim_message_id text nullable indexed
- sender text nullable indexed
- recipient text nullable indexed
- domain text indexed
- whm_account text indexed
- ip inet nullable indexed
- remote_mta text nullable
- smtp_code text nullable
- smtp_response text nullable
- status text nullable indexed (delivered, rejected, deferred, bounced, accepted)
- error_category text nullable indexed (policy, auth, rbl, mailboxfull, unknownuser, spam, timeout, dns, etc.)
- error_message text nullable
- meta jsonb nullable
- created_at/updated_at

B) auth_events
- id bigserial
- occurred_at timestamptz indexed
- proto enum: imap|pop3|smtp
- event_type text indexed (login_success, login_failed, logout, auth_failed)
- user_email text nullable indexed (ex: user@rmladv.com.br)
- domain text indexed
- whm_account text indexed
- ip inet nullable indexed
- auth_result enum: success|fail
- failure_reason text nullable indexed
- meta jsonb nullable
- created_at/updated_at

C) mailboxes (opcional)
- id bigserial
- whm_account text indexed
- domain text indexed
- email text indexed unique (ou unique composto domain+email)
- is_active boolean default true
- created_at/updated_at

D) cpanel_accounts (cadastro/integracao cPanel)
- id bigserial
- user_id foreign key users
- whm_account text indexed
- domain text indexed
- cpanel_username text nullable indexed
- cpanel_host text (base url)
- api_token_encrypted text (criptografado no banco, usar Crypt::encryptString)
- token_last_verified_at timestamptz nullable
- meta jsonb nullable
- created_at/updated_at

E) ingest_clients (para o serviço Python)
- id bigserial
- name text
- shared_secret_hash text (hash bcrypt/argon2 do secret) OU armazenar em env e comparar com hash
- is_active boolean default true
- created_at/updated_at

Ingestão (serviço Python -> Laravel):
- Implementar endpoint seguro para ingestão:
  POST /api/v1/ingest/events
  Headers:
    - X-Ingest-Secret: <PYTHON_INGEST_SHARED_SECRET> (ou um secret por client)
      Body:
      {
      "source": "python-agent",
      "domain": "rmladv.com.br",
      "whm_account": "rmlad479",
      "mail_events": [ ... ],
      "auth_events": [ ... ]
      }
- Validar payload com FormRequest:
    - limitar tamanho (ex: até 5k eventos por request)
    - validar campos, datas ISO8601, enums
- Inserir em batch (DB::transaction + insert) para performance.
- Responder com resumo: inseridos, inválidos, duplicados.
- Deduplicação MVP: usar índice único opcional:
    - mail_events: unique(exim_message_id, event_type, occurred_at, recipient) quando exim_message_id existir
    - auth_events: unique(user_email, event_type, occurred_at, ip) (opcional, melhor esforço)
- Rate limit específico para ingest: throttle alto, mas com secret.

Integração com cPanel (dentro do Laravel):
- Criar CpanelClientService (HTTP client) usando Guzzle/Http:: (Laravel HTTP Client).
- Suportar chamadas para:
    - validar token e listar contas/ domínios da conta
    - listar mailboxes do domínio (se aplicável ao seu plano)
- Implementar CpanelAccountService:
    - salvar/atualizar credenciais (token criptografado)
    - testar conectividade (ping/list accounts)
    - sincronizar mailboxes (opcional)

Cadastro e autenticação de usuários (API):
1) POST /api/v1/auth/register
- Entrada: name, email, password, whm_account (default env), domain (default env), cpanel_host, cpanel_api_token (ou outro método compatível)
- Fluxo:
  a) Criar user local (tabela users) com password hash.
  b) Validar credenciais cPanel chamando o CpanelClientService (ex: listar algo simples/“whoami”).
  c) Salvar/associar em cpanel_accounts com token criptografado.
  d) Retornar token Sanctum + user profile.
2) POST /api/v1/auth/token (login)
- Entrada: email, password
- Retorna token Sanctum.
3) POST /api/v1/auth/logout
- Revoga token atual.

Documentação Swagger/OpenAPI:
- Implementar Swagger para documentar TODOS endpoints.
- Preferência: pacote “l5-swagger” (ou “dedoc/scramble” se preferir) — escolha um e implemente completamente.
- Disponibilizar:
    - GET /api/docs (UI)
    - GET /api/openapi.json (spec)
- Documentar security schemes (Bearer token), exemplos de request/response e schemas.

Endpoints (rotas /api/v1):
Públicos:
1) GET /api/v1/health
- {status:"ok", version, time}

Auth:
2) POST /api/v1/auth/register
3) POST /api/v1/auth/token
4) POST /api/v1/auth/logout (auth:sanctum)

Ingest (python):
5) POST /api/v1/ingest/events (proteção via X-Ingest-Secret OU via Sanctum + habilidade “ingest”)
- MVP: usar X-Ingest-Secret

Consulta de eventos:
6) GET /api/v1/mail/events (auth:sanctum)
- lista paginada com filtros: start_at,end_at,domain,whm_account,direction,event_type,status,error_category,sender,recipient
7) GET /api/v1/auth/events (auth:sanctum)
- lista paginada com filtros: start_at,end_at,domain,whm_account,proto,auth_result,user_email,ip

Métricas:
8) GET /api/v1/metrics/overview (auth:sanctum)
- contagens agregadas no período:
  inbound_accepted, inbound_rejected,
  outbound_sent, outbound_deferred, outbound_bounced,
  login_success, login_failed,
  top_error_categories (mail) e top_failure_reasons (auth)
9) GET /api/v1/metrics/mail/series (auth:sanctum)
- séries por hour/day:
  inbound_accepted, inbound_rejected, outbound_sent, outbound_deferred, outbound_bounced
10) GET /api/v1/metrics/auth/series (auth:sanctum)
- séries por hour/day para login_success/login_failed por proto
11) GET /api/v1/metrics/queue (auth:sanctum)
- last_queue_depth + series baseado em event_type=queue_depth no mail_events

Mailboxes (opcional):
12) GET /api/v1/mailboxes (auth:sanctum)
13) POST /api/v1/mailboxes (auth:sanctum)
14) PATCH /api/v1/mailboxes/{id} (auth:sanctum)

Padrões de projeto:
- Controllers finos, sem query pesada.
- Services:
    - MailEventService (list + filtros)
    - AuthEventService (list + filtros)
    - MetricsService (overview + series + queue)
    - IngestService (validar + inserir em batch + dedupe)
    - CpanelClientService (HTTP calls)
    - CpanelAccountService (validate+store+sync)
- Requests:
    - RegisterRequest, LoginRequest
    - IngestEventsRequest
    - ListMailEventsRequest
    - ListAuthEventsRequest
    - MetricsOverviewRequest
    - MetricsSeriesRequest
    - MetricsQueueRequest
    - StoreMailboxRequest/UpdateMailboxRequest (opcional)
- Resources:
    - UserResource
    - MailEventResource
    - AuthEventResource
    - MetricSeriesResource (label, points[{t,value}])
    - OverviewResource
    - IngestResultResource

Performance:
- Índices nos campos filtrados com frequência (occurred_at, domain, whm_account, event_type, status, error_category, auth_result, proto, ip).
- Inserção em lote no ingest.
- Paginação default 50, máximo 200.

Segurança:
- Token Sanctum para usuários.
- Secret de ingest separado (X-Ingest-Secret) e comparação segura (hash_equals).
- Criptografar token do cPanel (Crypt::encryptString).
- Rate limiting:
    - Auth endpoints: throttle moderado
    - Ingest: throttle alto + secret
    - Queries: throttle padrão

Entrega do código:
- Gere a árvore de arquivos (paths) e o conteúdo principal de cada arquivo relevante:
    - routes/api.php
    - Controllers, Requests, Resources, Services
    - Models + migrations
    - Config swagger
    - README.md com:
        - instalação
        - configuração .env (Postgres, defaults domain/whm, cpanel config, ingest secret)
        - rodar migrations
        - acessar swagger
        - fluxo de cadastro via cPanel
        - exemplos curl (register/login, ingest, events, metrics)
- Inclua seeders/factories opcionais com dados fake realistas para testar o dashboard.
- Não implemente o agente Python aqui, apenas assuma que ele fará POST para /ingest/events com o payload descrito.

Observações importantes:
- Não implementar parsing de logs neste MVP, apenas a API para receber/armazenar/consultar.
- Escreva código limpo, com tipos e boas práticas, pronto para produção (MVP).
