#!/bin/bash
# FinFoco — Deploy automático para Hostinger via SCP + SSH
# Uso: bash deploy_hostinger.sh

set -e

# ─── CONFIGURAÇÃO ──────────────────────────────────────────────────────────────
SSH_USER="u137664132"
SSH_HOST="147.93.39.64"
SSH_PORT="65002"
REMOTE_APP="/home/${SSH_USER}/finfoco"     # pasta Laravel no servidor (fora de public_html)
REMOTE_PUBLIC="/home/${SSH_USER}/domains/finfoco.nexialabs.com.br/public_html"
LOCAL_DIR="/home/andre_gomes/finfoco-claude-code"
# ───────────────────────────────────────────────────────────────────────────────

# Cores
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✅ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $1${NC}"; }
err()  { echo -e "${RED}❌ $1${NC}"; exit 1; }

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FinFoco → Deploy Hostinger"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Solicitar credenciais se não configuradas
if [ -z "$SSH_USER" ] || [ -z "$SSH_HOST" ]; then
    read -p "Usuário SSH (ex: u123456789): " SSH_USER
    read -p "Host SSH (ex: srv12.hostinger.com): " SSH_HOST
    read -p "Porta SSH [22]: " SSH_PORT_INPUT
    SSH_PORT="${SSH_PORT_INPUT:-22}"
    REMOTE_APP="/home/${SSH_USER}/finfoco"
    REMOTE_PUBLIC="/home/${SSH_USER}/domains/finfoco.nexialabs.com.br/public_html"
fi

SSH_OPTS="-p ${SSH_PORT} -o StrictHostKeyChecking=accept-new"
SCP_OPTS="-P ${SSH_PORT} -o StrictHostKeyChecking=accept-new"

echo ""
warn "Você será solicitado a digitar a senha SSH algumas vezes."
echo ""

# ─── 1. Criar estrutura remota ──────────────────────────────────────────────────
echo "📁 Criando estrutura de pastas no servidor..."
ssh $SSH_OPTS ${SSH_USER}@${SSH_HOST} "mkdir -p ${REMOTE_APP}/{app,bootstrap,config,database,resources,routes,storage,public} ${REMOTE_APP}/storage/{app,framework,logs} ${REMOTE_APP}/storage/framework/{cache,sessions,views}"
ok "Estrutura criada"

# ─── 2. Upload do código Laravel (exceto vendor e public) ──────────────────────
echo ""
echo "📤 Enviando arquivos Laravel..."
rsync -avz --progress \
    --exclude='.env' \
    --exclude='vendor/' \
    --exclude='public/' \
    --exclude='node_modules/' \
    --exclude='.git/' \
    --exclude='storage/logs/*.log' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='finfocoDB_hostinger.sql' \
    -e "ssh $SSH_OPTS" \
    "${LOCAL_DIR}/" "${SSH_USER}@${SSH_HOST}:${REMOTE_APP}/"
ok "Código enviado"

# ─── 3. Upload do conteúdo de public/ → public_html/ ──────────────────────────
echo ""
echo "📤 Enviando arquivos públicos (public_html)..."
rsync -avz --progress \
    -e "ssh $SSH_OPTS" \
    "${LOCAL_DIR}/public/" "${SSH_USER}@${SSH_HOST}:${REMOTE_PUBLIC}/"
ok "Arquivos públicos enviados"

# ─── 4. Ajustar index.php no servidor ─────────────────────────────────────────
echo ""
echo "🔧 Ajustando index.php..."
ssh $SSH_OPTS ${SSH_USER}@${SSH_HOST} "
sed -i \"s|__DIR__.'/../vendor/autoload.php'|__DIR__.'/../../../finfoco/vendor/autoload.php'|g\" ${REMOTE_PUBLIC}/index.php
sed -i \"s|__DIR__.'/../bootstrap/app.php'|__DIR__.'/../../../finfoco/bootstrap/app.php'|g\" ${REMOTE_PUBLIC}/index.php
sed -i \"s|__DIR__.'/../storage/framework/maintenance.php'|__DIR__.'/../../../finfoco/storage/framework/maintenance.php'|g\" ${REMOTE_PUBLIC}/index.php
"
ok "index.php ajustado"

# ─── 5. Criar .env no servidor ────────────────────────────────────────────────
echo ""
warn "Criando .env no servidor (com credenciais reais)..."
ssh $SSH_OPTS ${SSH_USER}@${SSH_HOST} "cat > ${REMOTE_APP}/.env" << 'ENV_EOF'
APP_NAME=FinFoco
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://finfoco.nexialabs.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finfocoDB
DB_USERNAME=finfocoUser
DB_PASSWORD=Fla91681@

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
ENV_EOF
ok ".env criado"

# ─── 6. Comandos no servidor via SSH ──────────────────────────────────────────
echo ""
echo "⚙️  Executando comandos no servidor..."
ssh $SSH_OPTS ${SSH_USER}@${SSH_HOST} "
set -e
cd ${REMOTE_APP}

echo '→ Instalando dependências...'
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5

echo '→ Gerando APP_KEY...'
php artisan key:generate --force

echo '→ Limpando caches antigos...'
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo '→ Otimizando...'
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo '→ Ajustando permissões...'
chmod -R 775 storage bootstrap/cache

echo '→ Migrations (banco já importado via phpMyAdmin, pulando seed)...'
php artisan migrate --force 2>&1 | tail -3

echo 'Tudo pronto!'
"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
ok "Deploy concluído!"
echo ""
echo "  🌐 Acesse: https://finfoco.nexialabs.com.br"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
