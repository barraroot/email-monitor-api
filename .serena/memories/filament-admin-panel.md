# Filament v5 Admin Panel — Mail Monitor

## Setup concluído (2026-03-03)

### Pacotes instalados
- `filament/filament:^5.0`
- `spatie/laravel-permission:^7.2`

### Mudanças de tipos no Filament v5
Em Filament v5, propriedades estáticas em Resources têm tipos que divergem de `?string`:
- `$navigationIcon` → `string | BackedEnum | null` (usar `getNavigationIcon(): string` em vez do static)
- `$navigationGroup` → `string | UnitEnum | null` (usar `getNavigationGroup(): string` em vez do static)

### Estrutura de arquivos criados
- `app/Providers/Filament/AdminPanelProvider.php` — Panel provider
- `app/Filament/Resources/` — 6 resources (MailEvent, AuthEvent, Mailbox, CpanelAccount, IngestClient, User)
- `app/Filament/Widgets/` — StatsOverviewWidget, MailEventsChartWidget, AuthEventsChartWidget, QueueDepthChartWidget
- `app/Policies/` — 6 policies
- `database/seeders/RolesAndPermissionsSeeder.php` + `AdminUserSeeder.php`
- `database/migrations/2026_03_03_133211_create_permission_tables.php`
- `database/migrations/2026_03_03_133214_add_last_seen_at_to_ingest_clients_table.php`

### Roles/Permissions
- `admin` — todas
- `operator` — view_* + manage_mailboxes, manage_cpanel_accounts, manage_ingest_clients
- `viewer` — view_mail_events, view_auth_events, view_metrics

### Admin padrão
- Email: `ADMIN_EMAIL` env (default: admin@mailmonitor.local)
- Senha: `ADMIN_PASSWORD` env (default: password)

### Acesso
- Painel em: `/admin`
- Login: `/admin/login`
