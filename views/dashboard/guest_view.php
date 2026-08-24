<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enfant. | Escuela Primaria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        enfantBlue: '#0b2038',
                        enfantOrange: '#fc5c4c',
                    },
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .bg-hex-pattern {
            background-color: #f8f9fa;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l25.98 15v30L30 60 4.02 45V15z' fill-opacity='0.03' fill='%230b2038' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        .hero-overlay {
            background: rgba(11, 32, 56, 0.4);
        }
    </style>
</head>
<body class="text-gray-600 antialiased font-sans bg-white">

    <!-- Top Bar -->
    <div class="bg-enfantBlue text-white text-xs py-3 hidden md:block">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-xl tracking-tighter">enfant<span class="text-enfantOrange">.</span></span>
            </div>
            <div class="flex items-center space-x-10">
                <div class="flex items-center space-x-3">
                    <i class="fa-regular fa-compass text-2xl font-light"></i>
                    <div>
                        <p><span class="text-enfantOrange font-bold">ENCUÉNTRANOS:</span> Londres, Reino Unido</p>
                        <p class="text-gray-300">12-14 Kensington High Street</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fa-regular fa-clock text-2xl font-light"></i>
                    <div>
                        <p><span class="text-enfantOrange font-bold">HORARIO:</span> 09:00 - 17:00</p>
                        <p class="text-gray-300">Sábados y Domingos - Cerrado</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Mobile Logo -->
            <div class="md:hidden flex items-center space-x-2">
                <span class="font-bold text-2xl text-enfantBlue tracking-tighter">enfant<span class="text-enfantOrange">.</span></span>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex flex-1 justify-center space-x-8 text-[13px] font-bold text-enfantBlue">
                <a href="/" class="hover:text-enfantOrange transition flex items-center">Inicio</a>
                <a href="#services" class="hover:text-enfantOrange transition flex items-center">Nuestra Institución <i class="fa-solid fa-angle-down ml-1 text-[10px]"></i></a>
                <a href="#contact" class="hover:text-enfantOrange transition flex items-center">Contacto</a>
                <a href="/admissions.php" class="hover:text-enfantOrange transition flex items-center bg-enfantOrange/10 px-3 py-1 rounded-full text-enfantOrange">Admisiones / Aplicar</a>
                <a href="/auth/login.php" class="hover:text-enfantOrange transition flex items-center"><i class="fa-solid fa-lock mr-2 text-gray-400"></i> Mi Portal</a>
            </nav>

            <!-- Icons Right -->
            <div class="flex items-center space-x-6 text-enfantBlue border-l border-gray-200 pl-6 h-10">
                <a href="/auth/login.php" class="hover:text-enfantOrange transition"><i class="fa-solid fa-magnifying-glass"></i></a>
                <a href="/auth/login.php" class="hover:text-enfantOrange transition relative">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="absolute -top-2 -right-2 bg-enfantOrange text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section (Carousel) -->
    <div class="relative h-[600px] w-full overflow-hidden" id="hero-carousel">
        
        <!-- Slide 1 -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out opacity-100 slide">
            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=1200" alt="Niños en clase" class="w-full h-full object-cover object-center">
            <div class="absolute inset-0 hero-overlay"></div>
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="max-w-7xl mx-auto px-6 w-full text-white">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl md:text-3xl font-heading font-normal uppercase tracking-widest mb-4">Escuela Primaria Enfant</h2>
                        <h1 class="text-4xl md:text-6xl font-heading font-bold mb-8 leading-tight">ESTAMOS HACIENDO EL<br>MUNDO DE CADA NIÑO MEJOR</h1>
                        <a href="/auth/login.php" class="inline-block bg-enfantOrange hover:bg-opacity-90 text-white font-bold text-[13px] px-8 py-3.5 rounded-full uppercase tracking-wider transition">Contáctanos</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out opacity-0 slide">
            <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=1200" alt="Educación de calidad" class="w-full h-full object-cover object-center">
            <div class="absolute inset-0 hero-overlay"></div>
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="max-w-7xl mx-auto px-6 w-full text-white">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl md:text-3xl font-heading font-normal uppercase tracking-widest mb-4">Educación de Primer Nivel</h2>
                        <h1 class="text-4xl md:text-6xl font-heading font-bold mb-8 leading-tight">PREPARÁNDOLOS PARA<br>EL FUTURO</h1>
                        <a href="/auth/login.php" class="inline-block bg-enfantOrange hover:bg-opacity-90 text-white font-bold text-[13px] px-8 py-3.5 rounded-full uppercase tracking-wider transition">Descubre Más</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out opacity-0 slide">
            <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=1200" alt="Aprendiendo juntos" class="w-full h-full object-cover object-center">
            <div class="absolute inset-0 hero-overlay"></div>
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="max-w-7xl mx-auto px-6 w-full text-white">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl md:text-3xl font-heading font-normal uppercase tracking-widest mb-4">Desarrollo Integral</h2>
                        <h1 class="text-4xl md:text-6xl font-heading font-bold mb-8 leading-tight">APRENDIENDO JUNTOS<br>CADA DÍA</h1>
                        <a href="/auth/login.php" class="inline-block bg-enfantOrange hover:bg-opacity-90 text-white font-bold text-[13px] px-8 py-3.5 rounded-full uppercase tracking-wider transition">Únete Hoy</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carousel Controls -->
        <button class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-sm transition" onclick="prevSlide()">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-sm transition" onclick="nextSlide()">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>

    <!-- Script for Carousel -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        let slideInterval;
        
        function showSlide(index) {
            slides.forEach(slide => {
                slide.classList.remove('opacity-100');
                slide.classList.add('opacity-0');
            });
            slides[index].classList.remove('opacity-0');
            slides[index].classList.add('opacity-100');
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
            resetInterval();
        }
        
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
            resetInterval();
        }
        
        function resetInterval() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 6000);
        }
        
        // Auto advance every 6 seconds
        slideInterval = setInterval(nextSlide, 6000);
    </script>

    <!-- Our Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-heading font-normal text-gray-500 uppercase tracking-widest mb-2">Nuestros Servicios</h2>
                <p class="text-gray-500 text-lg font-light">Hacemos a tu hijo feliz día tras día</p>
                <div class="flex items-center justify-center mt-4">
                    <div class="w-12 h-[1px] bg-gray-300"></div>
                    <div class="w-3 h-3 rounded-full border-2 border-enfantOrange mx-2"></div>
                    <div class="w-12 h-[1px] bg-gray-300"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
                <!-- Left Column: Daily Care -->
                <div class="lg:col-span-4">
                    <h3 class="text-xl font-heading font-normal text-gray-500 uppercase tracking-widest mb-2">Nuestro Cuidado Diario</h3>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Educación popular para tu hijo</p>
                    <p class="text-gray-500 leading-relaxed mb-6">
                        Una educación integral y balanceada para el desarrollo óptimo de las capacidades cognitivas y sociales de sus hijos. Fomentamos un entorno de respeto, creatividad y excelencia académica.
                    </p>
                    <ul class="space-y-2 mb-8 text-gray-500 text-[15px]">
                        <li class="flex items-center"><i class="fa-solid fa-check-circle text-enfantOrange mr-3"></i> Programa de aprendizaje extraescolar</li>
                        <li class="flex items-center"><i class="fa-solid fa-check-circle text-enfantOrange mr-3"></i> Experimentos científicos</li>
                        <li class="flex items-center"><i class="fa-solid fa-check-circle text-enfantOrange mr-3"></i> Entorno de aprendizaje positivo</li>
                        <li class="flex items-center"><i class="fa-solid fa-check-circle text-enfantOrange mr-3"></i> Aprender jugando</li>
                        <li class="flex items-center"><i class="fa-solid fa-check-circle text-enfantOrange mr-3"></i> Atención individual y personalizada</li>
                    </ul>
                    <a href="#enrollment" class="inline-block border border-enfantOrange text-enfantOrange hover:bg-enfantOrange hover:text-white px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition">Más Info</a>
                </div>

                <!-- Right Column: 6 Premium Cards -->
                <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-orange-50 group-hover:bg-enfantOrange flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-school text-2xl text-enfantOrange group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-enfantOrange transition-colors">Excelentes Instalaciones</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Espacios modernos y seguros diseñados para el desarrollo óptimo.</p>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-blue-50 group-hover:bg-enfantBlue flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-flask text-2xl text-enfantBlue group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-enfantBlue transition-colors">Juegos Divertidos</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Áreas recreativas donde los niños desarrollan habilidades motoras.</p>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 group-hover:bg-emerald-500 flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-scroll text-2xl text-emerald-500 group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-emerald-500 transition-colors">Clases Variadas</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Plan de estudios que incluye arte, música, idiomas y tecnología.</p>
                    </div>
                    <!-- Card 4 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-purple-50 group-hover:bg-purple-500 flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-microscope text-2xl text-purple-500 group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-purple-500 transition-colors">Laboratorios</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Instalaciones científicas donde la curiosidad se hace práctica.</p>
                    </div>
                    <!-- Card 5 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-amber-50 group-hover:bg-amber-500 flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-ice-cream text-2xl text-amber-500 group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-amber-500 transition-colors">Comidas Sanas</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Menús balanceados preparados por nutricionistas expertos.</p>
                    </div>
                    <!-- Card 6 -->
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(252,92,76,0.15)] hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-14 h-14 rounded-full bg-rose-50 group-hover:bg-rose-500 flex items-center justify-center mb-5 transition-colors duration-300">
                            <i class="fa-solid fa-bus text-2xl text-rose-500 group-hover:text-white transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-heading text-sm text-enfantBlue font-bold uppercase tracking-wider mb-3 group-hover:text-rose-500 transition-colors">Rutas Escolares</h4>
                        <p class="text-gray-500 text-[13px] leading-relaxed">Flota segura con personal capacitado para traslados.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enrollment Steps Section -->
    <section id="enrollment" class="py-20 bg-hex-pattern">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-heading font-normal text-gray-500 uppercase tracking-widest mb-2">Inscripciones</h2>
                <p class="text-gray-500 text-lg font-light">Pasos realizados con corazón, alma, mente y fuerza</p>
                <div class="flex items-center justify-center mt-4">
                    <div class="w-12 h-[1px] bg-gray-300"></div>
                    <div class="w-3 h-3 rounded-full border-2 border-enfantOrange mx-2"></div>
                    <div class="w-12 h-[1px] bg-gray-300"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-center relative z-10">
                <!-- Decorative Line -->
                <div class="hidden lg:block absolute top-10 left-[12%] right-[12%] h-[1px] bg-gray-200 -z-10 border border-dashed border-gray-300"></div>
                
                <!-- Step 1 -->
                <div class="bg-transparent">
                    <div class="w-20 h-20 mx-auto bg-enfantBlue text-white rounded-full flex items-center justify-center text-2xl font-heading font-light mb-6 shadow-md">1</div>
                    <h4 class="font-heading text-lg text-gray-600 font-normal mb-3">Contacto</h4>
                    <p class="text-gray-500 text-sm leading-relaxed px-4">Comunícate con nosotros para agendar una visita o resolver cualquier duda inicial sobre nuestro programa.</p>
                </div>
                <!-- Step 2 -->
                <div class="bg-transparent">
                    <div class="w-20 h-20 mx-auto bg-enfantOrange text-white rounded-full flex items-center justify-center text-2xl font-heading font-light mb-6 shadow-md">2</div>
                    <h4 class="font-heading text-lg text-gray-600 font-normal mb-3">Solicitud</h4>
                    <p class="text-gray-500 text-sm leading-relaxed px-4">Completa el formulario de admisión con todos los datos requeridos del estudiante y sus padres.</p>
                </div>
                <!-- Step 3 -->
                <div class="bg-transparent">
                    <div class="w-20 h-20 mx-auto bg-enfantBlue text-white rounded-full flex items-center justify-center text-2xl font-heading font-light mb-6 shadow-md">3</div>
                    <h4 class="font-heading text-lg text-gray-600 font-normal mb-3">Orientación</h4>
                    <p class="text-gray-500 text-sm leading-relaxed px-4">Asiste a una entrevista personalizada para alinear expectativas y conocer nuestra metodología educativa.</p>
                </div>
                <!-- Step 4 -->
                <div class="bg-transparent">
                    <div class="w-20 h-20 mx-auto bg-enfantOrange text-white rounded-full flex items-center justify-center text-2xl font-heading font-light mb-6 shadow-md">4</div>
                    <h4 class="font-heading text-lg text-gray-600 font-normal mb-3">Admisión</h4>
                    <p class="text-gray-500 text-sm leading-relaxed px-4">Recibe la carta de aceptación oficial y únete a nuestra gran familia educativa.</p>
                </div>
            </div>

            <div class="mt-12 flex justify-center space-x-2">
                <button type="button" onclick="alert('Ya estás en el primer paso del proceso de inscripción.')" class="border border-enfantOrange text-enfantOrange px-6 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest hover:bg-enfantOrange hover:text-white transition">Anterior</button>
                <button type="button" onclick="alert('Sigue los 4 pasos mostrados arriba para completar tu inscripción.')" class="border border-enfantOrange text-enfantOrange px-6 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest hover:bg-enfantOrange hover:text-white transition">Siguiente</button>
            </div>
        </div>
    </section>

    <!-- First Step CTA Section -->
    <section class="py-16 bg-gray-100 border-t border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center space-x-6 mb-6 md:mb-0">
                <div class="w-20 h-20 rounded-full bg-white shadow-sm flex items-center justify-center text-enfantOrange text-3xl">
                    <i class="fa-regular fa-paper-plane"></i>
                </div>
                <div>
                    <h3 class="font-heading text-2xl text-gray-600 uppercase tracking-widest mb-1">Da El Primer Paso</h3>
                    <p class="text-enfantBlue font-medium">Un mundo de aprendices donde los niños ganan un pasaporte al mundo</p>
                </div>
            </div>
            <a href="/auth/login.php" class="border border-enfantOrange text-enfantOrange px-8 py-3 rounded-full text-[13px] font-bold uppercase tracking-widest hover:bg-enfantOrange hover:text-white transition">Contáctanos</a>
        </div>
    </section>

    <!-- Newsletter Footer Top -->
    <div id="contact" class="bg-white py-16 text-center border-b border-gray-100">
        <div class="max-w-3xl mx-auto px-6">
            <div class="w-24 h-24 mx-auto rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center text-enfantOrange text-4xl mb-6 shadow-sm">
                <i class="fa-regular fa-envelope-open"></i>
            </div>
            <h2 class="text-3xl font-heading font-normal text-gray-600 mb-8">¿Quieres saber de nosotros?</h2>
            <form onsubmit="event.preventDefault(); alert('¡Gracias por suscribirte! Te hemos enviado un correo de confirmación.');" class="flex justify-center max-w-lg mx-auto relative">
                <input type="email" required placeholder="Tu dirección de correo" class="w-full bg-gray-50 border border-gray-200 rounded-full px-6 py-4 focus:outline-none focus:border-enfantOrange">
                <button type="submit" class="absolute right-1 top-1 bottom-1 bg-enfantOrange text-white px-8 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-opacity-90 transition">Suscribirse</button>
            </form>
        </div>
    </div>

    <!-- Main Footer -->
    <footer class="bg-enfantBlue text-gray-300 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 border-b border-gray-700 pb-16 mb-10">
            <!-- About Us Column -->
            <div>
                <h4 class="font-heading text-[13px] text-white uppercase tracking-widest mb-6 pb-2 border-b-2 border-enfantOrange inline-block">Sobre Nosotros</h4>
                <p class="text-sm leading-relaxed mb-8">
                    Somos una institución educativa comprometida con la excelencia académica y el desarrollo integral. Nuestro enfoque humano garantiza que cada estudiante alcance su máximo potencial en un ambiente seguro y estimulante.
                </p>
                <div class="text-white text-3xl font-bold tracking-tighter">
                    enfant<span class="text-enfantOrange">.</span>
                    <p class="text-[10px] font-normal tracking-widest uppercase mt-1 text-gray-400">Escuela Primaria</p>
                </div>
            </div>

            <!-- Recent Posts Column -->
            <div>
                <h4 class="font-heading text-[13px] text-white uppercase tracking-widest mb-6 pb-2 border-b-2 border-enfantOrange inline-block">Publicaciones Recientes</h4>
                <ul class="space-y-6">
                    <?php if (empty($newsList)): ?>
                        <li class="text-sm">No hay publicaciones recientes.</li>
                    <?php else: ?>
                        <?php foreach($newsList as $news): ?>
                        <li class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-800 rounded-md overflow-hidden shrink-0">
                                <?php if (!empty($news['photo_url'])): ?>
                                    <img src="<?= htmlspecialchars($news['photo_url']) ?>" alt="Post" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-700"></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="#" class="text-white text-sm font-medium hover:text-enfantOrange transition leading-tight block mb-1"><?= htmlspecialchars($news['title']) ?></a>
                                <p class="text-enfantOrange text-sm font-bold"><?= date('d', strtotime($news['created_at'])) ?> <span class="text-[10px] text-gray-400 font-normal uppercase tracking-widest">/ <?= date('F', strtotime($news['created_at'])) ?></span></p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Features Column -->
            <div>
                <h4 class="font-heading text-[13px] text-white uppercase tracking-widest mb-6 pb-2 border-b-2 border-enfantOrange inline-block">Nuestras Características</h4>
                <p class="text-sm leading-relaxed mb-6">Ofrecemos soluciones integrales para la educación moderna. Cada detalle de nuestras instalaciones y metodología ha sido cuidadosamente planificado.</p>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Diseño limpio y amigable</li>
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Entorno ultra responsivo</li>
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Colores agradables</li>
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Iconos y material infantil</li>
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Plugins y herramientas premium</li>
                    <li class="flex items-center"><i class="fa-regular fa-circle-check text-enfantOrange mr-3"></i> Soporte dedicado 24/7</li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="flex justify-center space-x-4 mb-6">
                <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center text-gray-400 hover:border-enfantOrange hover:text-white hover:bg-enfantOrange transition"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center text-gray-400 hover:border-enfantOrange hover:text-white hover:bg-enfantOrange transition"><i class="fa-brands fa-youtube"></i></a>
                <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center text-gray-400 hover:border-enfantOrange hover:text-white hover:bg-enfantOrange transition"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center text-gray-400 hover:border-enfantOrange hover:text-white hover:bg-enfantOrange transition"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <p class="text-xs text-gray-500">© <?= date('Y') ?> Escuela Primaria Enfant creada con <i class="fa-regular fa-heart text-enfantOrange"></i> en Bucharest</p>
        </div>
    </footer>
</body>
</html>
