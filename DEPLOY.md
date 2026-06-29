# Deploy FinFoco → Hostinger

## Pré-requisitos
- Acesso SSH à Hostinger
- Banco MySQL criado no hPanel (anote: host, nome do banco, usuário, senha)
- Composer instalado no servidor (verifique: `composer --version`)

---

## Estrutura no servidor Hostinger

```
/home/u123456789/          ← raiz SSH (fora do public_html)
├── artisan
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env                   ← NUNCA commitar
└── public_html/           ← raiz pública do domínio
    ├── index.php          ← ajustar os caminhos (ver abaixo)
    ├── .htaccess
    └── ...outros assets
```

---

## Passo a passo

### 1. Upload dos arquivos Laravel (exceto public/)
Via FTP ou Gerenciador de Arquivos, suba TUDO exceto a pasta `public/`
para a raiz SSH: `/home/u123456789/`

### 2. Upload do conteúdo de public/ → public_html/
Suba o conteúdo de `public/` para `public_html/`.

### 3. Ajustar index.php no servidor
Edite `/home/u123456789/public_html/index.php` e ajuste os caminhos:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

### 4. Criar .env no servidor
Copie `.env.example` para `.env` e preencha com as credenciais reais:

```bash
cp .env.example .env
nano .env
```

### 5. Comandos via SSH
```bash
cd /home/u123456789

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Gerar chave da aplicação
php artisan key:generate

# Executar migrations
php artisan migrate --seed

# Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissões
chmod -R 775 storage bootstrap/cache
```

### 6. .htaccess no public_html
O arquivo já está em `public/.htaccess`. Garanta que está em `public_html/.htaccess`.

---

## Re-deploy (atualizações)
```bash
# Upload dos arquivos alterados via FTP

# Via SSH:
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Rollback
Restaure os arquivos anteriores via FTP e execute:
```bash
php artisan migrate:rollback
php artisan config:cache
```
