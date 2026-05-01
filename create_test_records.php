<?php
// /create_test_records.php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Insert a course if not exists
    $db->exec("INSERT OR IGNORE INTO `courses` (`id`, `name`, `description`) VALUES 
        (1, 'Décimo A', 'Curso de Décimo Grado - Sección A'),
        (2, 'Undécimo A', 'Curso de Undécimo Grado - Sección A')
    ");

    // 2. Insert test users
    $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
    
    // User for Teacher 1
    $db->exec("INSERT OR IGNORE INTO `users` (`id`, `role_id`, `document`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`) VALUES 
        (2, 4, '87654321', 'Carlos', 'Ortega', 'carlos@escuela.com', '5551234567', '{$hashedPassword}', 1)
    ");

    // User for Student 1
    $db->exec("INSERT OR IGNORE INTO `users` (`id`, `role_id`, `document`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`) VALUES 
        (3, 5, '11223344', 'Juan', 'Perez', 'juan@escuela.com', '5559876543', '{$hashedPassword}', 1)
    ");

    // 3. Insert teacher record
    $db->exec("INSERT OR IGNORE INTO `teachers` (`id`, `user_id`, `specialty`) VALUES 
        (1, 2, 'Matemáticas y Física')
    ");

    // 4. Insert student record
    $db->exec("INSERT OR IGNORE INTO `students` (`id`, `user_id`, `course_id`, `parent_user_id`, `birth_date`, `address`) VALUES 
        (1, 3, 1, NULL, '2010-05-12', 'Calle Principal 123')
    ");

    // 5. Insert subjects
    $db->exec("INSERT OR IGNORE INTO `subjects` (`id`, `name`, `description`, `course_id`, `teacher_id`, `weekly_hours`) VALUES 
        (1, 'Álgebra básica', 'Introducción a ecuaciones lineales', 1, 1, 4),
        (2, 'Trigonometría', 'Funciones trigonométricas', 2, 1, 4)
    ");

    echo "Sample test records successfully created in the SQLite database!\n";
} catch (Exception $e) {
    echo "Error inserting test records: " . $e->getMessage() . "\n";
}
?>
