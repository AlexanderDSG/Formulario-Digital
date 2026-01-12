# Sistema de Formularios Médicos Digitales

> Sistema integral de gestión de formularios médicos para hospitales y clínicas desarrollado con CodeIgniter 4

---

## Descripción General

### Objetivo del Sistema

Sistema de gestión de formularios médicos digitales diseñado para optimizar el flujo de trabajo en servicios de emergencias hospitalarias, permitiendo el registro, seguimiento y gestión integral de pacientes a través de diferentes etapas de atención médica.

### Problema que Resuelve

- **Digitalización de formularios médicos** anteriormente en papel
- **Eliminación de duplicación de datos** entre departamentos
- **Trazabilidad completa** del paciente desde admisión hasta alta
- **Acceso rápido** a historias clínicas y datos médicos
- **Generación automática** de reportes y documentación médica
- **Control de permisos** basado en roles de personal médico

### Alcance y Limitaciones

* Alcance:**
- Gestión completa de pacientes en servicios de emergencia
- Sistema de roles para 5 tipos de usuarios médicos
- Formularios médicos divididos en 16 secciones (A-P)
- Integración con base de datos hospitalaria externa (SQL Server)
- Generación de PDFs con información médica
- Búsqueda avanzada de pacientes por múltiples criterios

**Limitaciones:**
- Requiere conectividad a base de datos local (MySQL) y hospitalaria (SQL Server)
- Los IDs de tratamiento pueden no persistir correctamente entre transferencias de especialidades
- No incluye sistema de facturación o inventario de medicamentos
- Orientado específicamente a servicios de emergencia

---

## Características Principales

- **Autenticación segura** con tokens de sesión únicos y encriptación SHA-256
- **5 roles de usuario** con permisos granulares
- **Formularios seccionales** (A-P) para registro médico completo
- **Búsqueda dual** en base de datos local y hospitalaria
- **Reportes y estadísticas** para administración
- **Generación de PDFs** con plantillas médicas
- **Seguimiento de tratamientos** y medicación
- **Evolución de pacientes** con registro temporal
- **Triaje y clasificación** de emergencias
- **Interfaz responsive** con TailwindCSS

---

## Arquitectura y Tecnologías

### Stack Tecnológico

#### Backend
- **Framework:** CodeIgniter 4.x
- **Lenguaje:** PHP 8.1+
- **Base de Datos Principal:** MySQL 8.0+
- **Base de Datos Hospitalaria:** SQL Server (integración)

#### Frontend
- **CSS Framework:** TailwindCSS 3.x
- **JavaScript:** Vanilla JS + jQuery
- **Componentes UI:** Bootstrap 5 (componentes selectos)
- **Tablas de Datos:** DataTables
- **Generación PDF:** jsPDF

#### Herramientas
- **Gestor de Dependencias PHP:** Composer
- **Gestor de Dependencias JS:** npm
- **Control de Versiones:** Git

### Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────┐
│              NAVEGADOR WEB                      │
│  (HTML + TailwindCSS + JavaScript + jsPDF)      │
└───────────────────┬─────────────────────────────┘
                    │ HTTP/AJAX
┌───────────────────▼─────────────────────────────┐
│          CODEIGNITER 4 (MVC)                    │
│  ┌──────────────────────────────────────────┐   │
│  │  Controllers (por rol)                   │   │
│  │  • Administrador  • Admisiones           │   │
│  │  • Médicos        • Enfermería           │   │
│  │  • Especialidades                        │   │
│  └────────────┬─────────────────────────────┘   │
│               │                                  │
│  ┌────────────▼─────────────────────────────┐   │
│  │  Models (lógica de negocio)             │   │
│  │  • PacienteModel                         │   │
│  │  • AtencionModel                         │   │
│  │  • TratamientoModel                      │   │
│  │  • Secciones A-P                         │   │
│  └────────────┬─────────────────────────────┘   │
│               │                                  │
│  ┌────────────▼─────────────────────────────┐   │
│  │  Views (plantillas PHP)                  │   │
│  │  • Dashboards por rol                    │   │
│  │  • Formularios seccionales               │   │
│  │  • Componentes reutilizables             │   │
│  └──────────────────────────────────────────┘   │
└───────────────┬──────────────────┬───────────────┘
                │                  │
    ┌───────────▼────────┐  ┌──────▼──────────────┐
    │  MySQL (Local)     │  │  SQL Server (HSVP)  │
    │  Database Local    │  │  Hospital Database  │
    └────────────────────┘  └─────────────────────┘
```

### Patrón de Arquitectura MVC

1. **Routes** → Enrutamiento organizado por grupos de roles
2. **Filters** → Autenticación y autorización (`AuthFilter`)
3. **Controllers** → Lógica de aplicación separada por rol médico
4. **Models** → Acceso a datos y lógica de negocio
5. **Views** → Presentación modular con secciones reutilizables

---

## Estructura del Proyecto

```
Formulario-Digital/
│
├── app/                          # Código de la aplicación
│   ├── Config/
│   │   ├── Routes.php           # Definición de rutas
│   │   ├── Database.php         # Configuración de BD
│   │   └── Filters.php          # Registro de filtros
│   │
│   ├── Controllers/             # Controladores por rol
│   │   ├── Administrador/       # Gestión de usuarios, reportes
│   │   ├── Admisiones/          # Registro de pacientes
│   │   ├── Medicos/             # Atención médica, triaje
│   │   ├── Enfermeria/          # Administración de medicamentos
│   │   └── Especialidades/      # Atención especializada
│   │
│   ├── Models/                  # Modelos de datos
│   │   ├── PacienteModel.php   # Modelo central de pacientes (50+ campos)
│   │   ├── AtencionModel.php   # Episodios de atención
│   │   ├── Admision/
│   │   ├── Medicos/
│   │   │   └── GuardarSecciones/  # Modelos secciones A-P
│   │   ├── Enfermeria/
│   │   ├── Especialidades/
│   │   └── Administrador/
│   │
│   ├── Views/                   # Vistas del sistema
│   │   ├── formulario/
│   │   │   ├── seccion_a.php    # Sección A: Datos del establecimiento
│   │   │   ├── seccion_b.php    # Sección B: Datos del paciente
│   │   │   ├── ...              # Secciones C-P
│   │   │   └── especialidad/    # Formularios especializados
│   │   ├── admision/
│   │   ├── medicos/
│   │   ├── enfermeria/
│   │   └── administrador/
│   │
│   └── Filters/
│       └── AuthFilter.php       # Filtro de autenticación y roles
│
├── public/                      # Archivos públicos
│   ├── css/
│   │   └── tailwind.css        # CSS compilado (generado)
│   ├── js/
│   │   ├── admision/           # JS módulo de admisión
│   │   ├── medicos/            # JS módulo médico
│   │   ├── enfermeria/         # JS módulo enfermería
│   │   ├── especialidades/     # JS módulo especialidades
│   │   └── administrador/      # JS módulo administración
│   └── index.php               # Punto de entrada
│
├── src/
│   └── input.css               # Archivo fuente TailwindCSS
│
├── tests/                      # Pruebas automatizadas
│   ├── unit/
│   ├── database/
│   └── session/
│
├── writable/                   # Archivos generados
│   ├── logs/                   # Logs de la aplicación
│   ├── session/                # Datos de sesión
│   └── uploads/                # Archivos subidos
│
├── vendor/                     # Dependencias PHP (Composer)
├── node_modules/               # Dependencias JS (npm)
│
├── .env                        # Configuración de entorno (NO COMMITEAR)
├── composer.json               # Dependencias PHP
├── package.json                # Dependencias Node.js
├── tailwind.config.js          # Configuración TailwindCSS
├── phpunit.xml                 # Configuración de tests
└── README.md                   # Este archivo
```
---
