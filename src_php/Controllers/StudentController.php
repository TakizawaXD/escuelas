<?php

namespace App\Controllers;

use App\Helpers\SecurityHelper;
use App\Services\LoggerService;
use App\Validators\Validator;
use Database;
use Auth;
use Exception;

class StudentController extends BaseController
{
    /**
     * Muestra el formulario de matrícula y procesa su guardado.
     */
    public function enroll(): void
    {
        Auth::redirectIfNotAuth();
        if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
            header("Location: /index.php");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar CSRF
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
                $error = 'Token CSRF inválido.';
            } else {
                $db->beginTransaction();
                try {
                    $parent_type = $_POST['parent_type'] ?? 'none';
                    $parent_user_id = null;

                    // --- 1. PROCESAR ACUDIENTE ---
                    if ($parent_type === 'existing') {
                        $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
                    } elseif ($parent_type === 'new') {
                        $p_doc = Auth::sanitize($_POST['p_document'] ?? '');
                        $p_fn = Auth::sanitize($_POST['p_first_name'] ?? '');
                        $p_ln = Auth::sanitize($_POST['p_last_name'] ?? '');
                        $p_email = Auth::sanitize($_POST['p_email'] ?? '');
                        $p_phone = Auth::sanitize($_POST['p_phone'] ?? '');

                        if (!$p_doc || !$p_fn || !$p_email) {
                            throw new Exception("Faltan datos obligatorios del nuevo acudiente.");
                        }

                        // Verificar si ya existe
                        $stmt = $db->prepare("SELECT id FROM users WHERE document = ? OR email = ?");
                        $stmt->execute([$p_doc, $p_email]);
                        if ($stmt->fetch()) {
                            throw new Exception("El documento o correo del acudiente ya está registrado.");
                        }

                        // Contraseña por defecto: documento
                        $pwdHash = SecurityHelper::hashPassword($p_doc);
                        $stmt = $db->prepare("
                            INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) 
                            VALUES (6, ?, ?, ?, ?, ?, ?, 1)
                        ");
                        $stmt->execute([$p_doc, $p_fn, $p_ln, $p_email, $p_phone, $pwdHash]);
                        $parent_user_id = $db->lastInsertId();
                    }

                    // --- 2. PROCESAR CUENTA DE ESTUDIANTE ---
                    $create_type = $_POST['create_type'] ?? 'existing';
                    $user_id = null;

                    if ($create_type === 'new') {
                        $document = Auth::sanitize($_POST['document'] ?? '');
                        $first_name = Auth::sanitize($_POST['first_name'] ?? '');
                        $last_name = Auth::sanitize($_POST['last_name'] ?? '');
                        $email = Auth::sanitize($_POST['email'] ?? '');
                        $phone = Auth::sanitize($_POST['phone'] ?? '');
                        $password = $_POST['password'] ?? '';

                        if (!$document || !$first_name || !$email || !$password) {
                            throw new Exception("Faltan datos obligatorios del nuevo estudiante.");
                        }

                        $stmt = $db->prepare("SELECT id FROM users WHERE document = ? OR email = ?");
                        $stmt->execute([$document, $email]);
                        if ($stmt->fetch()) {
                            throw new Exception("El documento o correo del estudiante ya existe.");
                        }

                        $pwdHash = SecurityHelper::hashPassword($password);
                        $stmt = $db->prepare("
                            INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) 
                            VALUES (5, ?, ?, ?, ?, ?, ?, 1)
                        ");
                        $stmt->execute([$document, $first_name, $last_name, $email, $phone, $pwdHash]);
                        $user_id = $db->lastInsertId();
                    } else {
                        $user_id = intval($_POST['user_id'] ?? 0);
                        if (empty($user_id)) {
                            throw new Exception("Debe seleccionar un usuario estudiante existente.");
                        }
                    }

                    // --- 3. CREAR REGISTRO DE MATRÍCULA ---
                    $course_id = intval($_POST['course_id'] ?? 0);
                    $birth_date = Auth::sanitize($_POST['birth_date'] ?? '');
                    $address = Auth::sanitize($_POST['address'] ?? '');
                    $photo_url = Auth::sanitize($_POST['photo_url'] ?? '');
                    $grade = Auth::sanitize($_POST['grade'] ?? '');
                    $gpa = floatval($_POST['gpa'] ?? 0.00);
                    $scalability = Auth::sanitize($_POST['scalability'] ?? '');

                    if (empty($course_id) || empty($birth_date)) {
                        throw new Exception("El curso y la fecha de nacimiento son obligatorios.");
                    }

                    $stmt = $db->prepare("
                        INSERT INTO students (user_id, course_id, parent_user_id, birth_date, address, photo_url, grade, gpa, scalability)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $course_id, $parent_user_id, $birth_date, $address, $photo_url, $grade, $gpa, $scalability]);

                    // Registrar auditoría
                    LoggerService::logActivity(
                        Auth::user()['id'] ?? null,
                        'STUDENT_ENROLLED',
                        "Estudiante con ID de usuario $user_id fue matriculado en curso $course_id."
                    );

                    $db->commit();
                    header("Location: /modules/students/index.php");
                    exit;

                } catch (Exception $e) {
                    $db->rollBack();
                    $error = $e->getMessage();
                }
            }
        }

        // Cargar datos para la vista
        $courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();
        $studentUsers = $db->query("
            SELECT u.* FROM users u 
            LEFT JOIN students s ON u.id = s.user_id 
            WHERE u.role_id = 5 AND s.id IS NULL
            ORDER BY u.first_name ASC
        ")->fetchAll();
        $parentUsers = $db->query("SELECT * FROM users WHERE role_id = 6 ORDER BY first_name ASC")->fetchAll();

        // Renderizar layout
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/layout/sidebar.php';
        include __DIR__ . '/../../views/students/create_view.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
