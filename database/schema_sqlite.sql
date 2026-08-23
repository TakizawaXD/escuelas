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
  `tfa_secret` TEXT DEFAULT NULL,
  `tfa_enabled` INTEGER DEFAULT 0,
  `email_verified` INTEGER DEFAULT 0,
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
  `category_id` INTEGER DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `news_categories`(`id`) ON DELETE SET NULL
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

-- 15. permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `description` TEXT
);

-- 16. role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INTEGER NOT NULL,
  `permission_id` INTEGER NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
);

-- 17. payment_plans
CREATE TABLE IF NOT EXISTS `payment_plans` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `description` TEXT,
  `total_amount` REAL NOT NULL,
  `installments` INTEGER DEFAULT 1,
  `active` INTEGER DEFAULT 1
);

-- 18. news_categories
CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `color` TEXT DEFAULT '#4f46e5'
);

-- 19. login_attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `ip_address` TEXT NOT NULL,
  `attempts` INTEGER DEFAULT 1,
  `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. calendar_events
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `start_date` TEXT NOT NULL,
  `end_date` TEXT,
  `color` TEXT DEFAULT '#4f46e5',
  `user_id` INTEGER,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- 20. academic_years
CREATE TABLE IF NOT EXISTS `academic_years` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `start_date` TEXT NOT NULL,
  `end_date` TEXT NOT NULL,
  `active` INTEGER DEFAULT 0
);

-- 21. classrooms
CREATE TABLE IF NOT EXISTS `classrooms` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL UNIQUE,
  `capacity` INTEGER DEFAULT 30,
  `location` TEXT
);

-- 22. enrollments
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `course_id` INTEGER NOT NULL,
  `academic_year_id` INTEGER NOT NULL,
  `enrollment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT,
  UNIQUE(`student_id`, `academic_year_id`)
);

-- 23. schedules
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `course_id` INTEGER NOT NULL,
  `subject_id` INTEGER NOT NULL,
  `teacher_id` INTEGER NOT NULL,
  `classroom_id` INTEGER NOT NULL,
  `day_of_week` INTEGER NOT NULL,
  `start_time` TEXT NOT NULL,
  `end_time` TEXT NOT NULL,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE RESTRICT
);

-- 24. student_medical_records
CREATE TABLE IF NOT EXISTS `student_medical_records` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL UNIQUE,
  `blood_type` TEXT,
  `allergies` TEXT,
  `medical_conditions` TEXT,
  `medications` TEXT,
  `emergency_contact_name` TEXT,
  `emergency_contact_phone` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
);

-- 25. discipline_reports
CREATE TABLE IF NOT EXISTS `discipline_reports` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `teacher_id` INTEGER NOT NULL,
  `type` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT
);

-- 26. transport_routes
CREATE TABLE IF NOT EXISTS `transport_routes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `driver_name` TEXT NOT NULL,
  `vehicle_plate` TEXT NOT NULL,
  `capacity` INTEGER DEFAULT 40
);

-- 27. transport_assignments
CREATE TABLE IF NOT EXISTS `transport_assignments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `route_id` INTEGER NOT NULL,
  `student_id` INTEGER NOT NULL UNIQUE,
  `stop_name` TEXT NOT NULL,
  FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
);

-- 28. library_books
CREATE TABLE IF NOT EXISTS `library_books` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `author` TEXT NOT NULL,
  `isbn` TEXT,
  `total_copies` INTEGER DEFAULT 1,
  `available_copies` INTEGER DEFAULT 1,
  `pdf_path` TEXT
);

-- 29. library_loans
CREATE TABLE IF NOT EXISTS `library_loans` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `book_id` INTEGER NOT NULL,
  `user_id` INTEGER NOT NULL,
  `loan_date` TEXT NOT NULL,
  `due_date` TEXT NOT NULL,
  `return_date` TEXT,
  `status` TEXT DEFAULT 'Activo',
  FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

-- 30. exams
CREATE TABLE IF NOT EXISTS `exams` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `subject_id` INTEGER NOT NULL,
  `teacher_id` INTEGER NOT NULL,
  `title` TEXT NOT NULL,
  `exam_date` DATE NOT NULL,
  `max_score` REAL DEFAULT 100,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY(`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE
);

-- 31. exam_results
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `exam_id` INTEGER NOT NULL,
  `student_id` INTEGER NOT NULL,
  `score` REAL NOT NULL,
  `remarks` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY(`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
);

-- 32. certificates
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `student_id` INTEGER NOT NULL,
  `title` TEXT NOT NULL,
  `issue_date` DATE NOT NULL,
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
);

-- 33. inventory_categories
CREATE TABLE IF NOT EXISTS `inventory_categories` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL
);

-- 34. inventory_items
CREATE TABLE IF NOT EXISTS `inventory_items` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `category_id` INTEGER NOT NULL,
    `name` TEXT NOT NULL,
    `quantity` INTEGER DEFAULT 0,
    `status` TEXT DEFAULT 'Activo',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(`category_id`) REFERENCES `inventory_categories`(`id`)
);

-- 35. cafeteria_menus
CREATE TABLE IF NOT EXISTS `cafeteria_menus` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `day_of_week` TEXT NOT NULL,
    `meal_type` TEXT NOT NULL,
    `description` TEXT NOT NULL
);

-- 36. messages
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `sender_id` INTEGER NOT NULL,
    `receiver_id` INTEGER NOT NULL,
    `subject` TEXT NOT NULL,
    `body` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(`sender_id`) REFERENCES `users`(`id`),
    FOREIGN KEY(`receiver_id`) REFERENCES `users`(`id`)
);
CREATE TABLE IF NOT EXISTS `admission_applications` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `parent_first_name` TEXT NOT NULL,
  `parent_last_name` TEXT NOT NULL,
  `parent_email` TEXT NOT NULL,
  `parent_phone` TEXT NOT NULL,
  `student_first_name` TEXT NOT NULL,
  `student_last_name` TEXT NOT NULL,
  `target_grade` TEXT NOT NULL,
  `previous_school` TEXT,
  `status` TEXT NOT NULL DEFAULT 'Pendiente',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
