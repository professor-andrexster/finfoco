-- ============================================================
-- FinFoco — SQL completo para importar no phpMyAdmin Hostinger
-- Banco: finfocoDB | Usuário: finfocoUser
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- 1. Tabela: categories
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome`       VARCHAR(60)     NOT NULL,
  `cor`        VARCHAR(7)      NOT NULL DEFAULT '#6366F1',
  `icone`      VARCHAR(50)     NOT NULL DEFAULT 'tag',
  `tipo`       ENUM('entrada','saida','ambos') NOT NULL DEFAULT 'ambos',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. Tabela: transactions
-- ============================================================
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo`         ENUM('entrada','saida') NOT NULL,
  `valor`        DECIMAL(10,2)   NOT NULL,
  `descricao`    VARCHAR(60)     NOT NULL,
  `categoria_id` BIGINT UNSIGNED NULL,
  `data`         DATE            NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transactions_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `transactions_categoria_id_foreign`
    FOREIGN KEY (`categoria_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Tabela: alerts
-- ============================================================
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` BIGINT UNSIGNED NOT NULL,
  `limite_valor` DECIMAL(10,2)   NOT NULL,
  `periodo`      ENUM('dia','semana','mes') NOT NULL DEFAULT 'mes',
  `ativo`        TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `alerts_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `alerts_categoria_id_foreign`
    FOREIGN KEY (`categoria_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Tabela: bills (contas a pagar/receber)
-- ============================================================
DROP TABLE IF EXISTS `bills`;
CREATE TABLE `bills` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo`         ENUM('pagar','receber') NOT NULL,
  `descricao`    VARCHAR(60)     NOT NULL,
  `valor`        DECIMAL(10,2)   NOT NULL,
  `vencimento`   DATE            NOT NULL,
  `status`       ENUM('pendente','atrasado','pago','recebido') NOT NULL DEFAULT 'pendente',
  `categoria_id` BIGINT UNSIGNED NULL,
  `recorrente`   TINYINT(1)      NOT NULL DEFAULT 0,
  `recorrencia`  ENUM('semanal','mensal','anual') NULL,
  `pago_em`      DATE NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bills_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `bills_categoria_id_foreign`
    FOREIGN KEY (`categoria_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Tabela: reminders (lembretes)
-- ============================================================
DROP TABLE IF EXISTS `reminders`;
CREATE TABLE `reminders` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo`         VARCHAR(60)     NOT NULL,
  `data_lembrete`  DATE            NOT NULL,
  `concluido`      TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. Tabela: settings (configurações chave-valor)
-- ============================================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `chave`      VARCHAR(60)  NOT NULL,
  `valor`      TEXT         NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. Tabela de controle de migrations do Laravel
-- ============================================================
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255)  NOT NULL,
  `batch`     INT           NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2024_01_01_000001_create_categories_table', 1),
  ('2024_01_01_000002_create_transactions_table', 1),
  ('2024_01_01_000003_create_alerts_table', 1),
  ('2024_01_01_000004_create_bills_table', 1),
  ('2024_01_01_000005_create_reminders_table', 1),
  ('2024_01_01_000006_create_settings_table', 1);

-- ============================================================
-- 8. Seed: 6 categorias padrão
-- ============================================================
INSERT INTO `categories` (`nome`, `cor`, `icone`, `tipo`) VALUES
  ('Alimentação',  '#F59E0B', 'utensils',     'saida'),
  ('Moradia',      '#6366F1', 'home',          'saida'),
  ('Transporte',   '#3B82F6', 'car',           'saida'),
  ('Saúde',        '#22C55E', 'heart-pulse',   'saida'),
  ('Lazer',        '#EC4899', 'smile',         'saida'),
  ('Salário',      '#22C55E', 'banknote',      'entrada');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIM DO ARQUIVO
-- Banco: finfocoDB  |  Usuário: finfocoUser  |  App: FinFoco
-- ============================================================
