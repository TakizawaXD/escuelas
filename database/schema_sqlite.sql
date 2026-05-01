-- /database/schema_sqlite.sql

-- 1. roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE
);

-- 2. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `role_id` INTEGER NOT NULL,
  `document` TEXT NOT NULL UNIQUE,
  `first_name` TEXT NOT NULL,
  `last_name` TEXT NOT NULL,
  `email` TEXT NOT NULL UNIQUE,
  `phone` TEXT DEFAULT NULL,
  `password` TEXT NOT NULL,
  `status` INTEGER DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 3. courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `description` TEXT
);

-- 4. students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL UNIQUE,
  `course_id` INTEGER NOT NULL,
  `parent_user_id` INTEGER DEFAULT NULL,
  `birth_date` TEXT NOT NULL,
  `address` TEXT,
  `photo_url` TEXT DEFAULT NULL,
  `grade` TEXT DEFAULT NULL,
  `gpa` REAL DEFAULT 0.00,
  `scalability` TEXT DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

-- 5. teachers
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL UNIQUE,
  `specialty` TEXT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- 6. subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `description` TEXT,
  `course_id` INTEGER NOT NULL,
  `teacher_id` INTEGER DEFAULT NULL,
  `weekly_hours` INTEGER DEFAULT 4,
  `study_material` TEXT DEFAULT NULL,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
);

-- 7. grades
CREATE TABLE IF NOT EXISTS `grades` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `subject_id` INTEGER NOT NULL,
  `period` INTEGER NOT NULL,
  `exam_grade` REAL DEFAULT 0.00,
  `workshop_grade` REAL DEFAULT 0.00,
  `project_grade` REAL DEFAULT 0.00,
  `final_grade` REAL DEFAULT 0.00,
  `comments` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  UNIQUE(`student_id`, `subject_id`, `period`)
);

-- 8. attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `subject_id` INTEGER NOT NULL,
  `date` TEXT NOT NULL,
  `status` TEXT NOT NULL,
  `justification` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  UNIQUE(`student_id`, `subject_id`, `date`)
);

-- 9. payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `concept` TEXT NOT NULL,
  `amount` REAL NOT NULL,
  `status` TEXT NOT NULL DEFAULT 'Pendiente',
  `due_date` TEXT NOT NULL,
  `payment_date` TEXT DEFAULT NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
);

-- 10. notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `target_role_id` INTEGER DEFAULT NULL,
  `title` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
);

-- 11. security_tokens
CREATE TABLE IF NOT EXISTS `security_tokens` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `token` TEXT NOT NULL,
  `expires_at` TEXT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- 12. news
CREATE TABLE IF NOT EXISTS `news` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `content` TEXT NOT NULL,
  `photo_url` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 13. settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `app_name` TEXT NOT NULL,
  `logo_url` TEXT,
  `color_primary` TEXT,
  `color_secondary` TEXT
);

-- Seed Data
INSERT OR IGNORE INTO `roles` (`id`, `name`) VALUES
(1, 'ADMIN'),
(2, 'DIRECTOR'),
(3, 'COORDINADOR'),
(4, 'DOCENTE'),
(5, 'ESTUDIANTE'),
(6, 'PADRE');

INSERT OR IGNORE INTO `users` (`id`, `role_id`, `document`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`) VALUES
(1, 1, '12345678', 'Administrador', 'General', 'admin@escuela.com', '1234567890', '$2y$10$tjttbk.SEE14O76mqM7zuebYXPAWCmw.HLwSbLkSyRTtE7jxYIUb.', 1);

INSERT OR IGNORE INTO `settings` (`id`, `app_name`, `logo_url`, `color_primary`, `color_secondary`) VALUES
(1, 'SISTEMA ESCOLAR', '', '#059669', '#10b981');
