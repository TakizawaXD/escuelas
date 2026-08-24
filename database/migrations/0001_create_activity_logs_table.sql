-- Migration 0001: Crear tabla de logs de actividad
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER DEFAULT NULL,
  `action` TEXT NOT NULL,
  `ip_address` TEXT NOT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);
