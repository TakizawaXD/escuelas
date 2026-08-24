<div class="max-w-5xl mx-auto space-y-8 animate-fade-in pb-12 select-none">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Matricular Estudiante</h1>
            <p class="text-slate-500 font-medium text-sm mt-1">Completa los datos académicos y familiares del nuevo alumno.</p>
        </div>
        <a href="/modules/students/index.php" class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 hover:text-indigo-600 font-bold text-sm rounded-2xl border border-slate-200 shadow-sm transition active:scale-95">
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span>Volver al Listado</span>
        </a>
    </div>

    <!-- System Message Callouts -->
    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 text-sm font-semibold border border-rose-200/50 flex items-start space-x-3 shadow-sm animate-shake">
            <i class="fa-solid fa-circle-exclamation text-lg text-rose-500 shrink-0"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

        <!-- WIZARD STEPPER -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Stepper Line Connector (Desktop) -->
                <div class="hidden md:block absolute top-[28px] left-[10%] right-[10%] h-[2px] bg-slate-100 -z-10"></div>

                <!-- Step 1 Indicator -->
                <div class="flex items-center space-x-4 md:flex-col md:space-x-0 md:space-y-2 md:text-center md:flex-1">
                    <div id="step-badge-1" class="w-12 h-12 rounded-full bg-indigo-600 text-white font-extrabold flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-4 ring-indigo-50">1</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-widest">Cuenta</h4>
                        <p class="text-xs text-slate-400 font-medium">Asignar credenciales</p>
                    </div>
                </div>

                <!-- Step 2 Indicator -->
                <div class="flex items-center space-x-4 md:flex-col md:space-x-0 md:space-y-2 md:text-center md:flex-1">
                    <div id="step-badge-2" class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 font-extrabold flex items-center justify-center">2</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Academia</h4>
                        <p class="text-xs text-slate-400 font-medium">Curso y notas</p>
                    </div>
                </div>

                <!-- Step 3 Indicator -->
                <div class="flex items-center space-x-4 md:flex-col md:space-x-0 md:space-y-2 md:text-center md:flex-1">
                    <div id="step-badge-3" class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 font-extrabold flex items-center justify-center">3</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Familia</h4>
                        <p class="text-xs text-slate-400 font-medium">Acudiente y contacto</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 1 PANEL: STUDENT ACCOUNT -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-slate-900 px-8 py-5 border-b border-slate-800 flex items-center space-x-3 text-white">
                <i class="fa-solid fa-user-gear text-indigo-400"></i>
                <h3 class="font-bold tracking-wide uppercase text-sm">Paso 1: Cuenta y Datos del Estudiante</h3>
            </div>
            
            <div class="p-8 space-y-8">
                <!-- Large Radio Cards Selector -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                    <!-- Option 1: Existing Student -->
                    <div id="card-student-existing" onclick="selectStudentType('existing')" class="cursor-pointer border-2 border-indigo-600 bg-indigo-50/10 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4">
                        <input type="radio" name="create_type" id="radio-student-existing" value="existing" checked class="hidden">
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-user-clock"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Estudiante Existente</span>
                            <span class="block text-xs text-slate-500 mt-1">Vincular un usuario estudiante que ya está en la base de datos pero no tiene matrícula.</span>
                        </div>
                    </div>

                    <!-- Option 2: New Student -->
                    <div id="card-student-new" onclick="selectStudentType('new')" class="cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4">
                        <input type="radio" name="create_type" id="radio-student-new" value="new" class="hidden">
                        <div class="w-12 h-12 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-xl shrink-0" id="icon-student-new">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Crear Nuevo Alumno</span>
                            <span class="block text-xs text-slate-500 mt-1">Registrar un usuario desde cero en la plataforma junto con su matrícula escolar.</span>
                        </div>
                    </div>
                </div>

                <!-- Existing Student Dropdown -->
                <div id="panel-student-existing" class="max-w-xl mx-auto space-y-2">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Seleccionar Usuario Estudiante *</label>
                    <select name="user_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione...</option>
                        <?php foreach ($studentUsers as $usr): ?>
                            <option value="<?= $usr['id'] ?>"><?= htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name'] . ' (C.C/D.I: ' . $usr['document'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- New Student Form Inputs -->
                <div id="panel-student-new" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto border-t border-slate-100 pt-6">
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Documento / Identidad *</label>
                        <input type="text" name="document" placeholder="Ej. 10987654" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Correo Electrónico *</label>
                        <input type="email" name="email" placeholder="alumno@escuela.com" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Nombres Completos *</label>
                        <input type="text" name="first_name" placeholder="Nombres" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Apellidos Completos *</label>
                        <input type="text" name="last_name" placeholder="Apellidos" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Teléfono de Contacto</label>
                        <input type="text" name="phone" placeholder="Celular o fijo" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Contraseña Inicial *</label>
                        <input type="password" name="password" placeholder="Contraseña de ingreso" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2 PANEL: ACADEMIC INFO -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-slate-900 px-8 py-5 border-b border-slate-800 flex items-center space-x-3 text-white">
                <i class="fa-solid fa-graduation-cap text-emerald-400"></i>
                <h3 class="font-bold tracking-wide uppercase text-sm">Paso 2: Asignación Académica</h3>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Curso / Año Lectivo *</label>
                    <select name="course_id" required class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione el curso base...</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Fecha de Nacimiento *</label>
                    <input type="date" name="birth_date" required class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">URL Foto de Perfil (Opcional)</label>
                    <input type="url" name="photo_url" placeholder="https://ejemplo.com/foto.jpg" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Grado / Nomenclatura Interna</label>
                    <input type="text" name="grade" placeholder="Ej. A, B, Avanzado..." class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition">
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Dirección de Residencia</label>
                    <input type="text" name="address" placeholder="Ej. Calle 123 #45-67" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition">
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Escalabilidad (Notas de traslado / Admisión)</label>
                    <textarea name="scalability" rows="3" placeholder="Añadir observaciones adicionales..." class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 3 PANEL: GUARDIAN INFO -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-slate-900 px-8 py-5 border-b border-slate-800 flex items-center space-x-3 text-white">
                <i class="fa-solid fa-users text-rose-400"></i>
                <h3 class="font-bold tracking-wide uppercase text-sm">Paso 3: Información Familiar / Acudiente</h3>
            </div>

            <div class="p-8 space-y-8">
                <!-- Three-way Radio Card Selector -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Option 1: No Guardian -->
                    <div id="card-parent-none" onclick="selectParentType('none')" class="cursor-pointer border-2 border-rose-500 bg-rose-50/10 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3">
                        <input type="radio" name="parent_type" id="radio-parent-none" value="none" checked class="hidden">
                        <div class="w-10 h-10 bg-rose-100 text-rose-500 rounded-xl flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Sin Acudiente</span>
                            <span class="block text-[11px] text-slate-500 mt-1">El estudiante no requiere acudiente directo.</span>
                        </div>
                    </div>

                    <!-- Option 2: Existing Guardian -->
                    <div id="card-parent-existing" onclick="selectParentType('existing')" class="cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3">
                        <input type="radio" name="parent_type" id="radio-parent-existing" value="existing" class="hidden">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-lg shrink-0" id="icon-parent-existing">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Buscar Acudiente</span>
                            <span class="block text-[11px] text-slate-500 mt-1">Asociar a un acudiente ya registrado.</span>
                        </div>
                    </div>

                    <!-- Option 3: New Guardian -->
                    <div id="card-parent-new" onclick="selectParentType('new')" class="cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3">
                        <input type="radio" name="parent_type" id="radio-parent-new" value="new" class="hidden">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-lg shrink-0" id="icon-parent-new">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Nuevo Acudiente</span>
                            <span class="block text-[11px] text-slate-500 mt-1">Registrar un nuevo acudiente express.</span>
                        </div>
                    </div>
                </div>

                <!-- Existing Parent Dropdown -->
                <div id="panel-parent-existing" class="hidden max-w-xl mx-auto space-y-2">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Seleccionar Acudiente *</label>
                    <select name="parent_user_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione...</option>
                        <?php foreach ($parentUsers as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name'] . ' (C.C/D.I: ' . $p['document'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- New Parent Form Inputs -->
                <div id="panel-parent-new" class="hidden border border-slate-100 bg-slate-50/20 p-8 rounded-3xl max-w-4xl mx-auto space-y-6 relative">
                    <div class="absolute -top-3 left-6 bg-rose-500 text-white text-[10px] font-extrabold uppercase tracking-widest px-4 py-1 rounded-full shadow-md shadow-rose-500/10">Creación Express</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Documento / ID *</label>
                            <input type="text" name="p_document" placeholder="Cédula de ciudadanía" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                            <p class="text-[10px] text-slate-400 font-medium">Nota: El ID será la contraseña por defecto del acudiente.</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Correo Electrónico *</label>
                            <input type="email" name="p_email" placeholder="acudiente@correo.com" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Nombres *</label>
                            <input type="text" name="p_first_name" placeholder="Nombres del acudiente" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Apellidos</label>
                            <input type="text" name="p_last_name" placeholder="Apellidos del acudiente" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                        </div>
                        <div class="md:col-span-2 space-y-1">
                            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500">Teléfono Móvil</label>
                            <input type="text" name="p_phone" placeholder="Celular de contacto" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none text-sm font-medium transition">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Button -->
        <div class="flex justify-end pt-4">
            <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-slate-900 hover:bg-black text-white font-extrabold rounded-2xl transition text-base shadow-xl shadow-slate-900/10 hover:-translate-y-1 active:translate-y-0 flex items-center justify-center space-x-3 active:scale-98">
                <i class="fa-solid fa-user-check text-lg"></i>
                <span>Finalizar Matrícula</span>
            </button>
        </div>
    </form>
</div>

<!-- Dynamic Interactivity Script -->
<script>
function selectStudentType(type) {
    const cardExisting = document.getElementById('card-student-existing');
    const cardNew = document.getElementById('card-student-new');
    const iconNew = document.getElementById('icon-student-new');
    const radioExisting = document.getElementById('radio-student-existing');
    const radioNew = document.getElementById('radio-student-new');

    const panelExisting = document.getElementById('panel-student-existing');
    const panelNew = document.getElementById('panel-student-new');

    if (type === 'existing') {
        radioExisting.checked = true;
        cardExisting.className = "cursor-pointer border-2 border-indigo-600 bg-indigo-50/10 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4";
        cardNew.className = "cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4";
        iconNew.className = "w-12 h-12 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-xl shrink-0";
        
        panelExisting.classList.remove('hidden');
        panelNew.classList.add('hidden');
        
        // Update stepper indicator state
        document.getElementById('step-badge-1').className = "w-12 h-12 rounded-full bg-indigo-600 text-white font-extrabold flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-4 ring-indigo-50";
    } else {
        radioNew.checked = true;
        cardNew.className = "cursor-pointer border-2 border-indigo-600 bg-indigo-50/10 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4";
        cardExisting.className = "cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-indigo-600 transition flex items-start space-x-4";
        iconNew.className = "w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl shrink-0";
        
        panelNew.classList.remove('hidden');
        panelExisting.classList.add('hidden');
    }
}

function selectParentType(type) {
    const cardNone = document.getElementById('card-parent-none');
    const cardExisting = document.getElementById('card-parent-existing');
    const cardNew = document.getElementById('card-parent-new');
    const iconExisting = document.getElementById('icon-parent-existing');
    const iconNew = document.getElementById('icon-parent-new');

    const radioNone = document.getElementById('radio-parent-none');
    const radioExisting = document.getElementById('radio-parent-existing');
    const radioNew = document.getElementById('radio-parent-new');

    const panelExisting = document.getElementById('panel-parent-existing');
    const panelNew = document.getElementById('panel-parent-new');

    // Deselect all
    cardNone.className = "cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
    cardExisting.className = "cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
    cardNew.className = "cursor-pointer border-2 border-slate-200 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
    iconExisting.className = "w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-lg shrink-0";
    iconNew.className = "w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-lg shrink-0";

    panelExisting.classList.add('hidden');
    panelNew.classList.add('hidden');

    if (type === 'none') {
        radioNone.checked = true;
        cardNone.className = "cursor-pointer border-2 border-rose-500 bg-rose-50/10 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
        // Update badge 3
        document.getElementById('step-badge-3').className = "w-12 h-12 rounded-full bg-slate-100 text-slate-400 font-extrabold flex items-center justify-center";
    } else if (type === 'existing') {
        radioExisting.checked = true;
        cardExisting.className = "cursor-pointer border-2 border-rose-500 bg-rose-50/10 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
        iconExisting.className = "w-10 h-10 bg-rose-100 text-rose-500 rounded-xl flex items-center justify-center text-lg shrink-0";
        panelExisting.classList.remove('hidden');
        
        document.getElementById('step-badge-3').className = "w-12 h-12 rounded-full bg-rose-500 text-white font-extrabold flex items-center justify-center shadow-lg shadow-rose-500/20 ring-4 ring-rose-50";
    } else if (type === 'new') {
        radioNew.checked = true;
        cardNew.className = "cursor-pointer border-2 border-rose-500 bg-rose-50/10 p-5 rounded-2xl hover:border-rose-500 transition flex items-start space-x-3";
        iconNew.className = "w-10 h-10 bg-rose-100 text-rose-500 rounded-xl flex items-center justify-center text-lg shrink-0";
        panelNew.classList.remove('hidden');

        document.getElementById('step-badge-3').className = "w-12 h-12 rounded-full bg-rose-500 text-white font-extrabold flex items-center justify-center shadow-lg shadow-rose-500/20 ring-4 ring-rose-50";
    }
}

// Simple dynamic styling based on form input updates to light up the Stepper steps
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    
    // Highlight step 2 once Course is filled
    const courseSelect = document.getElementsByName('course_id')[0];
    const birthDateInput = document.getElementsByName('birth_date')[0];
    
    function checkStep2() {
        if (courseSelect.value && birthDateInput.value) {
            document.getElementById('step-badge-2').className = "w-12 h-12 rounded-full bg-emerald-500 text-white font-extrabold flex items-center justify-center shadow-lg shadow-emerald-500/20 ring-4 ring-emerald-50";
        } else {
            document.getElementById('step-badge-2').className = "w-12 h-12 rounded-full bg-slate-100 text-slate-400 font-extrabold flex items-center justify-center";
        }
    }
    
    courseSelect.addEventListener('change', checkStep2);
    birthDateInput.addEventListener('change', checkStep2);
});
</script>
