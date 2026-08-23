<?php
// /modules/medical_records/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    header("Location: /modules/medical_records/index.php");
    exit;
}

// Check student exists
$stmt = $db->prepare("
    SELECT s.id, u.first_name, u.last_name, u.document 
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: /modules/medical_records/index.php");
    exit;
}

// Fetch existing record if any
$stmt = $db->prepare("SELECT * FROM student_medical_records WHERE student_id = ?");
$stmt->execute([$student_id]);
$record = $stmt->fetch();
$is_new = !$record;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_type = Auth::sanitize($_POST['blood_type'] ?? '');
    $allergies = Auth::sanitize($_POST['allergies'] ?? '');
    $medical_conditions = Auth::sanitize($_POST['medical_conditions'] ?? '');
    $medications = Auth::sanitize($_POST['medications'] ?? '');
    $emergency_contact_name = Auth::sanitize($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_phone = Auth::sanitize($_POST['emergency_contact_phone'] ?? '');

    if (empty($emergency_contact_name) || empty($emergency_contact_phone)) {
        $error = 'Los datos del contacto de emergencia son obligatorios.';
    } else {
        if ($is_new) {
            $stmt = $db->prepare("
                INSERT INTO student_medical_records 
                (student_id, blood_type, allergies, medical_conditions, medications, emergency_contact_name, emergency_contact_phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$student_id, $blood_type, $allergies, $medical_conditions, $medications, $emergency_contact_name, $emergency_contact_phone])) {
                header("Location: /modules/medical_records/index.php");
                exit;
            } else {
                $error = 'Error al crear la ficha médica.';
            }
        } else {
            $stmt = $db->prepare("
                UPDATE student_medical_records 
                SET blood_type = ?, allergies = ?, medical_conditions = ?, medications = ?, emergency_contact_name = ?, emergency_contact_phone = ?
                WHERE student_id = ?
            ");
            if ($stmt->execute([$blood_type, $allergies, $medical_conditions, $medications, $emergency_contact_name, $emergency_contact_phone, $student_id])) {
                header("Location: /modules/medical_records/index.php");
                exit;
            } else {
                $error = 'Error al actualizar la ficha médica.';
            }
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight"><?= $is_new ? 'Crear' : 'Editar' ?> Ficha Médica</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Estudiante: <span class="font-bold text-slate-800"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></span> (<?= htmlspecialchars($student['document']) ?>)</p>
    </div>
    <a href="/modules/medical_records/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver al Listado</span>
    </a>
</div>

<div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group max-w-4xl">
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        
        <!-- Sección Clínica -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">
                <i class="fa-solid fa-notes-medical text-rose-500 mr-2"></i> Información Clínica
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Grupo Sanguíneo</label>
                    <select name="blood_type" class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">Desconocido / No aplica</option>
                        <?php 
                        $btypes = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                        $current = $record['blood_type'] ?? '';
                        foreach($btypes as $bt) {
                            $sel = ($current === $bt) ? 'selected' : '';
                            echo "<option value=\"$bt\" $sel>$bt</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Alergias (Alimenticias o Medicamentosas)</label>
                    <input type="text" name="allergies" placeholder="Ej. Penicilina, Maní, etc. (Dejar en blanco si no tiene)" value="<?= htmlspecialchars($record['allergies'] ?? '') ?>"
                           class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Condiciones Médicas / Enfermedades Crónicas</label>
                    <textarea name="medical_conditions" rows="3" placeholder="Ej. Asma, Diabetes..." 
                              class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"><?= htmlspecialchars($record['medical_conditions'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Medicación Actual Permanente</label>
                    <textarea name="medications" rows="3" placeholder="Especificar medicamento y dosis si aplica..." 
                              class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"><?= htmlspecialchars($record['medications'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Sección Emergencia -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">
                <i class="fa-solid fa-phone-volume text-rose-500 mr-2"></i> Contacto de Emergencia Obligatorio
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombre Completo y Parentesco <span class="text-rose-500">*</span></label>
                    <input type="text" name="emergency_contact_name" required placeholder="Ej. María López (Madre)" value="<?= htmlspecialchars($record['emergency_contact_name'] ?? '') ?>"
                           class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Teléfono de Emergencia <span class="text-rose-500">*</span></label>
                    <input type="text" name="emergency_contact_phone" required placeholder="Ej. +34 600 000 000" value="<?= htmlspecialchars($record['emergency_contact_phone'] ?? '') ?>"
                           class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-rose-500/20 hover:shadow-rose-500/30 transition active:scale-[0.98] flex items-center space-x-2">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Guardar Ficha Médica</span>
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
