-- Añadir campos a users (SQLite altera tabla no soporta múltiples ADD COLUMN, hay que hacerlo uno a uno)
ALTER TABLE `users` ADD COLUMN `tfa_secret` TEXT DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `tfa_enabled` INTEGER DEFAULT 0;
ALTER TABLE `users` ADD COLUMN `email_verified` INTEGER DEFAULT 0;

-- Crear tabla permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `description` TEXT
);

-- Crear tabla role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INTEGER NOT NULL,
  `permission_id` INTEGER NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
);

-- Crear tabla payment_plans
CREATE TABLE IF NOT EXISTS `payment_plans` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `description` TEXT,
  `total_amount` REAL NOT NULL,
  `installments` INTEGER DEFAULT 1,
  `active` INTEGER DEFAULT 1
);

-- Añadir category_id a news, así que creamos categories primero
CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `color` TEXT DEFAULT '#4f46e5'
);

ALTER TABLE `news` ADD COLUMN `category_id` INTEGER DEFAULT NULL REFERENCES `news_categories`(`id`) ON DELETE SET NULL;
