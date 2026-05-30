/**
 * Emerson Plancarte - Interactive Portfolio Script
 * Language Management, Animations, and Interactive Project Widgets
 */

// --- 1. Language Dictionaries & State ---
let currentLang = localStorage.getItem('portfolio_lang') || 'es';

const translations = {
  es: {
    nav_home: "Inicio",
    nav_about: "Acerca de mí",
    nav_experience: "Trayectoria",
    nav_portfolio: "Proyectos",
    nav_contact: "Contacto",

    hero_tag: "Sistemas Embebidos y Software",
    hero_title_1: "Emerson Salvador",
    hero_title_2: "Plancarte Cerecedo",
    hero_desc: "Ingeniero en Sistemas Computacionales con experiencia en desarrollo de sistemas embebidos, automatización de procesos, aplicaciones web integradas y administración de bases de datos. Soluciones sólidas alineadas a estándares técnicos.",
    btn_portfolio: "Ver Proyectos",
    btn_contact: "Contactar",

    about_title: "Acerca de Mí",
    about_intro: "Perfil Profesional",
    about_desc: "Profesional de ingeniería con experiencia en desarrollo tecnológico, automatización de procesos y optimización operativa. Especializado en dar solución a problemas técnicos complejos mediante soluciones estructuradas y robustas. \n Con sólidos conocimientos en desarrollo web/móvil, bases de datos y diseño electrónico para impulsar la eficiencia operativa y la calidad de los servicios mediante un enfoque analítico y orientado a resultados.",
    cred_lbl_edu: "Educación",
    cred_edu_sub: "Lic. en Sistemas Computacionales",
    cred_lbl_gpa: "Promedio",
    cred_gpa_sub: "GPA Equivalente: 3.7",
    cred_lbl_lang: "Idiomas",
    cred_lang_val: "Bilingüe",
    cred_lang_sub: "Certificación TOEFL certificada",
    cred_lbl_loc: "Ubicación",
    cred_loc_sub: "Guerrero, México",

    skills_header: "Habilidades Clave",
    skills_cat_embedded: "Sistemas Embebidos y Hardware",
    skills_cat_software: "Programación y Web",
    skills_cat_data: "Bases de Datos e Infraestructura",

    experience_title: "Experiencia y Logros",
    exp1_company: "CFE Zona de Transmisión Guerrero Morelos",
    exp1_role: "Residente de Ingeniería en Sistemas Computacionales",
    exp1_point1: "Diseñé e implementé un sistema web de evaluación del Índice de Calidad de Voltaje (ICV) conforme al Código de Red nacional, optimizando los reportes de más de 50 subestaciones.",
    exp1_point2: "Integré la plataforma con sistemas industriales PI System (OSIsoft) mediante C#, PHP y AJAX, reduciendo el tiempo de análisis de calidad de voltaje en más de un 80%.",
    exp1_point3: "Desarrollé paneles de monitoreo e interfaces intuitivas que automatizan el filtrado manual, disminuyendo errores de auditoría humana.",
    exp1_point4: "Realicé mantenimiento preventivo y correctivo en servidores locales y estaciones de trabajo críticas, logrando una disponibilidad de sistemas superior al 99.9%.",
    exp2_company: "Concurso de Innovación Tecnológica",
    exp2_role: "1er Lugar Local - Categoría Ciudades Inteligentes",
    exp2_point1: "Diseñé y programé \"TecAssist: Asistente Virtual\", un agente inteligente enfocado en el soporte del entorno local escolar y asistencia de trámites urbanos/institucionales.",
    exp2_point2: "Implementé técnicas de procesamiento de lenguaje y menús dinámicos con persistencia en base de datos PostgreSQL, premiado por su utilidad práctica e integración tecnológica.",

    portfolio_title: "Proyectos Destacados",
    port_badge_system: "Infraestructura",
    port_badge_ai: "Inteligencia Artificial",
    port_badge_hardware: "Hardware & IoT",

    port1_title: "Simulador de Código de Red (ICV)",
    port1_desc: "Auditoría automática de calidad de voltaje basada en límites del Código de Red de México.",
    port2_title: "TecAssist - Asistente Virtual",
    port2_desc: "Agente automatizado interactivo que responde sobre la experiencia y habilidades de Emerson.",
    port3_title: "ESP32 Telemetría de Sensores",
    port3_desc: "Osciloscopio y graficador de ondas de sensores analógicos IoT en tiempo real.",

    widget_icv_slider: "Voltaje de Entrada:",
    widget_icv_lbl_nom: "Línea Nominal",
    widget_icv_lbl_dev: "Desviación",
    widget_icv_status_ok: "Voltaje Estable (Conforme)",
    widget_icv_status_fail: "¡Violación Detectada! Fuera de límites",
    widget_icv_log_init: "Sistema inicializado en subestación de 115 kV. Monitoreo activo.",
    widget_icv_log_change: "Cambio registrado: {v} kV. Desviación: {d}%.",
    widget_icv_log_warn: "ALERTA: Voltaje fuera de rango tolerado (Código de Red).",

    widget_chat_opt1: "¿Habilidades?",
    widget_chat_opt2: "¿Experiencia CFE?",
    widget_chat_opt3: "¿Contacto?",
    widget_chat_bot_welcome: "¡Hola! Soy TecAssist. Haz clic en una opción abajo para consultarme sobre Emerson.",
    widget_chat_ans_skills: "Emerson domina C#, C/C++, Python, PHP, SQL y JS. Diseña firmware para ESP32/Arduino y gestiona bases de datos locales/nube.",
    widget_chat_ans_exp: "En CFE desarrolló una web para auditar la calidad de voltaje, reduciendo el análisis en más del 80%. Integrado con OSIsoft PI.",
    widget_chat_ans_contact: "Puedes contactar a Emerson en emersonplancarte@gmail.com o llamar al +52 (744) 447 3905. ¡Está disponible!",

    widget_tele_sensor: "Señal de Entrada (ADC):",
    widget_tele_status: "Simulando...",
    widget_tele_sine: "Senoidal",
    widget_tele_square: "Cuadrada",
    widget_tele_noisy: "Ruido",

    contact_title: "Contacto",
    contact_subtitle: "Hablemos",
    contact_desc: "¿Tienes alguna consulta técnica, deseas colaborar en un proyecto o estás interesado en conocer más sobre mis calificaciones? Contáctame a través del formulario o mis canales de contacto directo.",
    contact_lbl_phone: "Teléfono",
    contact_lbl_address: "Dirección",

    form_lbl_name: "Nombre completo",
    form_lbl_subject: "Asunto",
    form_lbl_msg: "Mensaje",
    form_btn_send: "Enviar Mensaje",
    form_success: "¡Mensaje enviado con éxito! Emerson se pondrá en contacto pronto.",
    form_error_email: "Por favor, ingresa un correo electrónico válido.",
    form_error_fields: "Por favor, llena todos los campos obligatorios.",

    footer_rights: "Todos los derechos reservados.",
    footer_lang: "Bilingüe (ES/EN)"
  },
  en: {
    nav_home: "Home",
    nav_about: "About me",
    nav_experience: "Experience",
    nav_portfolio: "Projects",
    nav_contact: "Contact",

    hero_tag: "Embedded Systems & Software",
    hero_title_1: "Emerson Salvador",
    hero_title_2: "Plancarte Cerecedo",
    hero_desc: "Computer Systems Engineer experienced in embedded systems design, process automation, integrated web solutions, and database management. Solid implementations aligned with technical codes.",
    btn_portfolio: "View Projects",
    btn_contact: "Contact",

    about_title: "About Me",
    about_intro: "Professional Profile",
    about_desc: "I am an engineer focused on solving complex technical challenges through structured and robust solutions. I combine electronic design, web/mobile software development, and database administration to automate tasks, optimize efficiency, and audit service quality in utilities and critical infrastructures.",
    cred_lbl_edu: "Education",
    cred_edu_sub: "B.S. Computer Systems Engineering",
    cred_lbl_gpa: "GPA Score",
    cred_gpa_sub: "Equivalent GPA: 3.7 / 4.0",
    cred_lbl_lang: "Languages",
    cred_lang_val: "Bilingual",
    cred_lang_sub: "Certified TOEFL credentials available",
    cred_lbl_loc: "Location",
    cred_loc_sub: "Guerrero, Mexico",

    skills_header: "Key Skills",
    skills_cat_embedded: "Embedded Systems & Hardware",
    skills_cat_software: "Programming & Web",
    skills_cat_data: "Databases & Infrastructure",

    experience_title: "Experience & Achievements",
    exp1_company: "CFE Guerrero Morelos Transmission Zone",
    exp1_role: "Computer Systems Engineering Resident",
    exp1_point1: "Designed and implemented an advanced web application to automate the Grid Code compliance auditing of Voltage Quality Index (ICV) across over 50 regional substations.",
    exp1_point2: "Integrated the web dashboard with OSIsoft PI System industrial databases using C#, PHP, and AJAX, cutting analysis time by over 80%.",
    exp1_point3: "Delivered custom visualization panels and intuitive user flows, reducing human error in regulatory compliance tracking.",
    exp1_point4: "Handled preventive and corrective maintenance for local utility servers and mission-critical networks, supporting a >99.9% systems availability.",
    exp2_company: "Technological Innovation Contest",
    exp2_role: "1st Place Local Winner - Smart Cities Category",
    exp2_point1: "Architected and programmed 'TecAssist: Virtual Assistant', an intelligent agent designed to automate student support and simplify local bureaucratic tasks.",
    exp2_point2: "Engineered context-based parsing and dynamic navigation steps connected to a PostgreSQL backend database, awarded for its immediate practicality.",

    portfolio_title: "Featured Projects",
    port_badge_system: "Infrastructure",
    port_badge_ai: "Artificial Intelligence",
    port_badge_hardware: "Hardware & IoT",

    port1_title: "Grid Code Auditor (ICV)",
    port1_desc: "Automated transmission line voltage quality auditing according to Mexico's Grid Code standards.",
    port2_title: "TecAssist - Virtual Assistant",
    port2_desc: "Interactive automated chatbot preview providing fast queries regarding Emerson's skillset.",
    port3_title: "ESP32 Sensors Telemetry",
    port3_desc: "Real-time ADC sensor waveform visualizer and digital signal plotter simulator.",

    widget_icv_slider: "Input Voltage:",
    widget_icv_lbl_nom: "Nominal Line",
    widget_icv_lbl_dev: "Deviation",
    widget_icv_status_ok: "Stable Voltage (Compliant)",
    widget_icv_status_fail: "Violation Detected! Out of tolerances",
    widget_icv_log_init: "Auditor online at 115 kV substation. Monitoring active.",
    widget_icv_log_change: "Value altered: {v} kV. Deviation: {d}%.",
    widget_icv_log_warn: "ALERT: Voltage beyond Grid Code allowed boundary.",

    widget_chat_opt1: "Skills?",
    widget_chat_opt2: "CFE Exp?",
    widget_chat_opt3: "Contact?",
    widget_chat_bot_welcome: "Hello! I am TecAssist. Click a button below to ask questions about Emerson.",
    widget_chat_ans_skills: "Emerson excels in C#, C/C++, Python, PHP, SQL, and JS. He designs ESP32/Arduino systems and handles local/cloud database architecture.",
    widget_chat_ans_exp: "At CFE he engineered a compliance web tool for voltage index assessment, trimming audits by 80% with OSIsoft PI integration.",
    widget_chat_ans_contact: "You can write to Emerson at emersonplancarte@gmail.com or call +52 (744) 447 3905. He is open for business!",

    widget_tele_sensor: "Input Signal (ADC):",
    widget_tele_status: "Simulating...",
    widget_tele_sine: "Sine Wave",
    widget_tele_square: "Square Wave",
    widget_tele_noisy: "Noisy",

    contact_title: "Contact",
    contact_subtitle: "Get in touch",
    contact_desc: "Have a technical query, looking to collaborate on a software integration, or interested in recruiting? Feel free to use the form or reach out directly.",
    contact_lbl_phone: "Phone",
    contact_lbl_address: "Address",

    form_lbl_name: "Full name",
    form_lbl_subject: "Subject",
    form_lbl_msg: "Message",
    form_btn_send: "Send Message",
    form_success: "Message sent! Emerson will get back to you shortly.",
    form_error_email: "Please enter a valid email address.",
    form_error_fields: "Please fill out all mandatory fields.",

    footer_rights: "All rights reserved.",
    footer_lang: "Bilingual (EN/ES)"
  }
};

function updateTranslations() {
  const elements = document.querySelectorAll('[data-translate-key]');
  elements.forEach(element => {
    const key = element.getAttribute('data-translate-key');
    if (translations[currentLang] && translations[currentLang][key]) {
      // Handle button input tags differently if any exist
      if (element.tagName === 'INPUT' && (element.type === 'submit' || element.type === 'button')) {
        element.value = translations[currentLang][key];
      } else {
        element.textContent = translations[currentLang][key];
      }
    }
  });

  // Update document metadata lang
  document.documentElement.lang = currentLang;

  // Update language toggle button text
  const langToggle = document.getElementById('langToggle');
  langToggle.textContent = currentLang === 'es' ? 'EN' : 'ES';
  langToggle.title = currentLang === 'es' ? 'Switch to English' : 'Cambiar a Español';
}

// Language toggle handler
document.getElementById('langToggle').addEventListener('click', () => {
  currentLang = currentLang === 'es' ? 'en' : 'es';
  localStorage.setItem('portfolio_lang', currentLang);
  updateTranslations();

  // Re-initialize dynamic text states inside widgets
  initIcvWidgetTranslations();
});


// --- 2. Mobile Menu & Active Navigation Scroll Highlights ---
const menuToggle = document.getElementById('menuToggle');
const navMenu = document.getElementById('navMenu');

menuToggle.addEventListener('click', () => {
  navMenu.classList.toggle('active');
  const icon = menuToggle.querySelector('i');
  if (navMenu.classList.contains('active')) {
    icon.className = 'fa-solid fa-xmark';
  } else {
    icon.className = 'fa-solid fa-bars';
  }
});

// Close mobile menu on nav link clicks
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    navMenu.classList.remove('active');
    menuToggle.querySelector('i').className = 'fa-solid fa-bars';
  });
});

// Scroll Listener for Header height and Scroll Spy
const header = document.querySelector('.header');
const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
  // Shrink header background on scroll
  if (window.scrollY > 50) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }

  // Active section indicator link updates (Scroll Spy)
  let currentActiveId = '';
  sections.forEach(sec => {
    const secTop = sec.offsetTop - 120;
    const secHeight = sec.clientHeight;
    if (window.scrollY >= secTop && window.scrollY < secTop + secHeight) {
      currentActiveId = sec.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') === `#${currentActiveId}`) {
      link.classList.add('active');
    }
  });
});


// --- 3. Scroll Reveal Entrance Animations ---
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
    }
  });
}, {
  threshold: 0.15
});

document.querySelectorAll('.reveal').forEach(el => {
  revealObserver.observe(el);
});


// --- 4. Light/Dark Theme Switcher ---
const themeToggle = document.getElementById('themeToggle');

// Load stored theme or default to dark
let activeTheme = localStorage.getItem('portfolio_theme') || 'dark';
document.documentElement.setAttribute('data-theme', activeTheme);
updateThemeIcon();

themeToggle.addEventListener('click', () => {
  activeTheme = activeTheme === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', activeTheme);
  localStorage.setItem('portfolio_theme', activeTheme);
  updateThemeIcon();
});

function updateThemeIcon() {
  const icon = themeToggle.querySelector('i');
  if (activeTheme === 'dark') {
    icon.className = 'fa-solid fa-sun';
  } else {
    icon.className = 'fa-solid fa-moon';
  }
}


// --- 5. Interactive Widget 1: ICV Analyzer Simulator ---
const icvVoltageSlider = document.getElementById('icvVoltageSlider');
const icvVoltageVal = document.getElementById('icvVoltageVal');
const icvDevVal = document.getElementById('icvDevVal');
const icvStatus = document.getElementById('icvStatus');
const icvLog = document.getElementById('icvLog');

const icvNominal = 115.0;
let icvLogsArray = [];

function initIcvWidgetTranslations() {
  // Update static layout calculations inside simulation
  const voltage = parseFloat(icvVoltageSlider.value);
  updateIcvSimulation(voltage, false);
}

function updateIcvSimulation(voltage, writeLog = true) {
  // Compute metrics
  icvVoltageVal.textContent = `${voltage.toFixed(1)} kV`;

  const devPercent = ((voltage - icvNominal) / icvNominal) * 100;
  icvDevVal.textContent = `${devPercent >= 0 ? '+' : ''}${devPercent.toFixed(2)}%`;

  // Grid code limit auditing: Standard boundary is ±5% voltage deviation
  const isCompliant = Math.abs(devPercent) <= 5.0;

  if (isCompliant) {
    icvStatus.className = "icv-status-indicator status-compliant";
    icvStatus.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${translations[currentLang].widget_icv_status_ok}</span>`;
  } else {
    icvStatus.className = "icv-status-indicator status-violation";
    icvStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <span>${translations[currentLang].widget_icv_status_fail}</span>`;
  }

  if (writeLog) {
    const now = new Date();
    const timestamp = now.toTimeString().split(' ')[0];
    let logMsg = "";

    if (isCompliant) {
      logMsg = translations[currentLang].widget_icv_log_change
        .replace('{v}', voltage.toFixed(1))
        .replace('{d}', devPercent.toFixed(2));
    } else {
      logMsg = `⚠️ ${translations[currentLang].widget_icv_log_warn} (V: ${voltage.toFixed(1)} kV, D: ${devPercent.toFixed(2)}%)`;
    }

    addIcvLog(`[${timestamp}] ${logMsg}`);
  }
}

function addIcvLog(message) {
  icvLogsArray.unshift(message);
  if (icvLogsArray.length > 4) {
    icvLogsArray.pop();
  }
  icvLog.innerHTML = icvLogsArray.join("<br>");
}

icvVoltageSlider.addEventListener('input', (e) => {
  const value = parseFloat(e.target.value);
  updateIcvSimulation(value, true);
});


// --- 6. Interactive Widget 2: TecAssist Chat Bot Mock ---
const chatMessages = document.getElementById('chatMessages');
const chatOptionBtns = document.querySelectorAll('.chat-option-btn');

chatOptionBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const action = btn.getAttribute('data-chat-action');
    const userText = btn.textContent;

    // User bubble
    appendChatMessage(userText, 'user');

    // Disable inputs momentarily
    setChatButtonsDisabled(true);

    // Bot responds with timeout
    setTimeout(() => {
      let botResponse = "";
      if (action === 'skills') {
        botResponse = translations[currentLang].widget_chat_ans_skills;
      } else if (action === 'experience') {
        botResponse = translations[currentLang].widget_chat_ans_exp;
      } else if (action === 'contact') {
        botResponse = translations[currentLang].widget_chat_ans_contact;
      }
      appendChatMessage(botResponse, 'bot');
      setChatButtonsDisabled(false);
    }, 600);
  });
});

function appendChatMessage(text, sender) {
  const msgEl = document.createElement('div');
  msgEl.className = `chat-msg chat-msg-${sender}`;
  msgEl.textContent = text;
  chatMessages.appendChild(msgEl);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function setChatButtonsDisabled(disabled) {
  chatOptionBtns.forEach(btn => {
    btn.disabled = disabled;
    btn.style.opacity = disabled ? '0.5' : '1';
    btn.style.pointerEvents = disabled ? 'none' : 'auto';
  });
}


// --- 7. Interactive Widget 3: ESP32 IoT Live Telemetry Graph ---
const canvas = document.getElementById('telemetryCanvas');
const ctx = canvas.getContext('2d');
const waveBtns = document.querySelectorAll('.telemetry-btn');
const telemetryStatus = document.getElementById('telemetryStatus');

let currentWaveType = 'sine';
let animFrameId = null;
let offset = 0;

// Setup canvas bounds
function resizeCanvas() {
  const rect = canvas.parentElement.getBoundingClientRect();
  canvas.width = rect.width;
  canvas.height = rect.height;
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas();

function drawTelemetry() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const width = canvas.width;
  const height = canvas.height;
  const midY = height / 2;
  const amplitude = height * 0.3;
  const frequency = 0.03;

  // Grid background lines
  ctx.strokeStyle = activeTheme === 'dark' ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.03)';
  ctx.lineWidth = 1;
  for (let x = 0; x < width; x += 30) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, height);
    ctx.stroke();
  }
  for (let y = 0; y < height; y += 20) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(width, y);
    ctx.stroke();
  }

  // Draw waveform signal
  ctx.beginPath();
  ctx.strokeStyle = 'hsl(152, 100%, 50%)'; // Electric Mint Green
  ctx.lineWidth = 2.5;
  ctx.shadowBlur = activeTheme === 'dark' ? 10 : 0;
  ctx.shadowColor = 'hsl(152, 100%, 50%)';

  for (let x = 0; x < width; x++) {
    let y = midY;
    const t = x * frequency + offset;

    if (currentWaveType === 'sine') {
      y = midY + Math.sin(t) * amplitude;
    } else if (currentWaveType === 'square') {
      y = midY + Math.sign(Math.sin(t)) * amplitude;
    } else if (currentWaveType === 'random') {
      y = midY + (Math.sin(t) + (Math.random() - 0.5) * 0.7) * amplitude;
    }

    if (x === 0) {
      ctx.moveTo(x, y);
    } else {
      ctx.lineTo(x, y);
    }
  }
  ctx.stroke();

  // Reset shadow
  ctx.shadowBlur = 0;

  // Draw simulated hardware reading dot at current head
  ctx.beginPath();
  ctx.arc(width - 5, midY + Math.sin((width - 5) * frequency + offset) * amplitude, 6, 0, Math.PI * 2);
  ctx.fillStyle = 'hsl(184, 100%, 50%)';
  ctx.fill();

  offset += 0.05;
  animFrameId = requestAnimationFrame(drawTelemetry);
}

waveBtns.forEach(btn => {
  btn.addEventListener('click', (e) => {
    waveBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentWaveType = btn.getAttribute('data-wave');

    // Status visual update
    if (currentWaveType === 'random') {
      telemetryStatus.className = 'telemetry-status scanning';
      telemetryStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <span>Noisy</span>`;
    } else {
      telemetryStatus.className = 'telemetry-status';
      telemetryStatus.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> <span>${translations[currentLang].widget_tele_status}</span>`;
    }
  });
});

drawTelemetry();


// --- 8. Contact Form Validator & Form Submission mock ---
const contactForm = document.getElementById('contactForm');
const formAlert = document.getElementById('formAlert');

contactForm.addEventListener('submit', (e) => {
  e.preventDefault();

  const name = document.getElementById('nameInput').value.trim();
  const email = document.getElementById('emailInput').value.trim();
  const subject = document.getElementById('subjectInput').value.trim();
  const message = document.getElementById('messageInput').value.trim();

  // Simple email regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!name || !email || !subject || !message) {
    showFormMessage(translations[currentLang].form_error_fields, 'error');
    return;
  }

  if (!emailRegex.test(email)) {
    showFormMessage(translations[currentLang].form_error_email, 'error');
    return;
  }

  // Simulate server sending
  const submitBtn = contactForm.querySelector('.submit-btn');
  const originalBtnHTML = submitBtn.innerHTML;

  submitBtn.disabled = true;
  submitBtn.style.opacity = '0.7';
  submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...`;

  setTimeout(() => {
    showFormMessage(translations[currentLang].form_success, 'success');
    contactForm.reset();
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    submitBtn.innerHTML = originalBtnHTML;
  }, 1200);
});

function showFormMessage(message, type) {
  formAlert.textContent = message;
  formAlert.style.display = 'block';
  if (type === 'success') {
    formAlert.className = 'form-alert success';
  } else {
    formAlert.className = 'form-alert status-violation'; // reuse error warning banner styling
    formAlert.style.background = 'rgba(255, 51, 102, 0.08)';
    formAlert.style.border = '1px solid rgba(255, 51, 102, 0.25)';
    formAlert.style.color = 'hsl(345, 100%, 60%)';
  }

  // Auto hide after 6 seconds
  setTimeout(() => {
    formAlert.style.display = 'none';
  }, 6000);
}


// --- 9. Initializer ---
document.addEventListener('DOMContentLoaded', () => {
  // Load initial translation values
  updateTranslations();

  // Set default initial logs in widget
  const defaultLog = translations[currentLang].widget_icv_log_init;
  addIcvLog(`[04:00:00] ${defaultLog}`);

  // Fit canvas once layout rendering stabilizes
  setTimeout(resizeCanvas, 300);
});
