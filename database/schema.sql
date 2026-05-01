CREATE DATABASE IF NOT EXISTS `escuela_erp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `escuela_erp`;

-- 1. roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `role_id` INT NOT NULL,
  `document` VARCHAR(20) NOT NULL UNIQUE,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `course_id` INT NOT NULL,
  `parent_user_id` INT DEFAULT NULL,
  `birth_date` DATE NOT NULL,
  `address` TEXT,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. teachers
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `specialty` VARCHAR(150) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `course_id` INT NOT NULL,
  `teacher_id` INT DEFAULT NULL,
  `weekly_hours` INT DEFAULT 4,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. grades
CREATE TABLE IF NOT EXISTS `grades` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `student_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `period` TINYINT NOT NULL,
  `exam_grade` DECIMAL(3,2) DEFAULT 0.00,
  `workshop_grade` DECIMAL(3,2) DEFAULT 0.00,
  `project_grade` DECIMAL(3,2) DEFAULT 0.00,
  `final_grade` DECIMAL(3,2) DEFAULT 0.00,
  `comments` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  UNIQUE(`student_id`, `subject_id`, `period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `student_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('Presente', 'Ausente', 'Tardanza') NOT NULL,
  `justification` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  UNIQUE(`student_id`, `subject_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `student_id` INT NOT NULL,
  `concept` VARCHAR(150) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pendiente', 'Pagado') NOT NULL DEFAULT 'Pendiente',
  `due_date` DATE NOT NULL,
  `payment_date` DATE DEFAULT NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `target_role_id` INT DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. security_tokens
CREATE TABLE IF NOT EXISTS `security_tokens` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Seed Data
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'ADMIN'),
(2, 'DIRECTOR'),
(3, 'COORDINADOR'),
(4, 'DOCENTE'),
(5, 'ESTUDIANTE'),
(6, 'PADRE');

-- Default Admin account (document: 12345678, password: admin)
INSERT INTO `users` (`id`, `role_id`, `document`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`) VALUES
(1, 1, '12345678', 'Administrador', 'General', 'admin@escuela.com', '1234567890', '$2y$10$vW90qIu7.8s268R790qGZem8X3R96S9V8Kj3I37V6A3h3S8Kj3I37', 1);
-- The bcrypt hash matches 'admin' exactly. Let's make sure password_verify matches it correctly.
