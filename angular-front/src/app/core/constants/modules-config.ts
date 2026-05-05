// Mapeo de cada MÓDULO a sus permisos requeridos

export const MODULES_CONFIG = {
  // Módulo: Alumnos
  alumnos: {
    nombre: 'Alumnos',
    icon: '👨‍🎓',
    route: '/dashboard/academico/alumnos',
    permissions: [
      'ver_estudiantes',
      'crear_estudiante',
      'editar_estudiante',
      'eliminar_estudiante',
      'ver_ficha_medica',
      'editar_ficha_medica'
    ]
  },
  // Módulo: Niveles
  niveles: {
    nombre: 'Niveles',
    icon: '📚',
    route: '/dashboard/academico/niveles',
    permissions: ['ver_cursos', 'crear_curso', 'editar_curso', 'eliminar_curso']
  },
  // Módulo: Materias
  materias: {
    nombre: 'Materias',
    icon: '📖',
    route: '/dashboard/academico/materias',
    permissions: ['ver_materias', 'crear_materia', 'editar_materia', 'eliminar_materia']
  },
  // Módulo: Horarios
  horarios: {
    nombre: 'Horarios',
    icon: '📅',
    route: '/dashboard/academico/horarios',
    permissions: ['ver_horarios', 'crear_horario', 'editar_horario', 'eliminar_horario']
  },
  // Módulo: Calificaciones
  calificaciones: {
    nombre: 'Calificaciones',
    icon: '📊',
    route: '/dashboard/academico/calificaciones',
    permissions: ['ver_notas', 'registrar_notas', 'editar_notas', 'eliminar_notas']
  },
  // Módulo: Pagos
  pagos: {
    nombre: 'Pagos',
    icon: '💵',
    route: '/dashboard/financiero/pagos',
    permissions: ['ver_pagos', 'registrar_pago', 'anular_pago']
  },
  // Módulo: Becas
  becas: {
    nombre: 'Becas',
    icon: '🎓',
    route: '/dashboard/financiero/becas',
    permissions: ['ver_becas', 'crear_beca', 'editar_beca', 'asignar_beca']
  },
  // Módulo: Reportes Financieros
  reportesFinancieros: {
    nombre: 'Reportes Financieros',
    icon: '📊',
    route: '/dashboard/financiero/reportes-financieros',
    permissions: ['ver_reportes_financieros', 'ver_caja_diaria']
  },
  // Módulo: Bitácora
  bitacora: {
    nombre: 'Bitácora',
    icon: '📋',
    route: '/dashboard/seguridad/bitacora',
    permissions: ['ver_bitacora', 'exportar_bitacora']
  },
  // Módulo: Permisos
  permisos: {
    nombre: 'Permisos',
    icon: '🎚️',
    route: '/dashboard/seguridad/roles-permisos',
    permissions: ['asignar_permisos', 'ver_usuarios']
  },
  // Módulo: Administradores
  administradores: {
    nombre: 'Administradores',
    icon: '👨‍💼',
    route: '/dashboard/usuarios/administradores',
    permissions: ['ver_usuarios', 'crear_usuario', 'editar_usuario', 'eliminar_usuario']
  },
  // Módulo: Profesores
  profesores: {
    nombre: 'Profesores',
    icon: '👩‍🏫',
    route: '/dashboard/usuarios/profesores',
    permissions: ['ver_docentes', 'crear_docente', 'editar_docente', 'eliminar_docente']
  },
  // Módulo: Tutores
  tutores: {
    nombre: 'Tutores',
    icon: '👪',
    route: '/dashboard/usuarios/tutores',
    permissions: ['ver_tutores', 'crear_tutor', 'editar_tutor', 'eliminar_tutor']
  },
  // Módulo: Personal Administrativo
  personalAdministrativo: {
    nombre: 'Personal Administrativo',
    icon: '📋',
    route: '/dashboard/usuarios/personal-administrativo',
    permissions: ['ver_usuarios', 'crear_usuario', 'editar_usuario']
  },
  // Módulo: Panel Principal
  panelPrincipal: {
    nombre: 'Panel Principal',
    icon: '📊',
    route: '/dashboard/main',
    permissions: [] // Siempre visible
  },
  // Módulo: Mi Perfil
  miPerfil: {
    nombre: 'Mi Perfil',
    icon: '👤',
    route: '/dashboard/perfil',
    permissions: [] // Siempre visible
  },
  // Modulo: Asistencias
  asistencias: {
    nombre: 'Asistencias',
    icon: '📋',
    route: '/dashboard/asistencias',
    permissions: ['ver_asistencias', 'registrar_asistencia'] 
  }
};

// Configuración de SECCIONES con sus módulos
export const SECTIONS_CONFIG = [
  {
    nombre: 'PRINCIPAL',
    icon: '🏠',
    modulos: ['panelPrincipal']
  },
  {
    nombre: 'USUARIOS',
    icon: '👥',
    modulos: ['administradores', 'personalAdministrativo', 'profesores', 'tutores',]
  },
  {
    nombre: 'ACADÉMICO',
    icon: '📚',
    modulos: [ 'alumnos', 'niveles', 'materias', 'horarios', 'calificaciones', 'asistencias']
  },
  {
    nombre: 'FINANCIERO',
    icon: '💰',
    modulos: ['pagos', 'becas', 'reportesFinancieros']
  },
  {
    nombre: 'SEGURIDAD',
    icon: '🔐',
    modulos: ['bitacora', 'permisos']
  },
  {
    nombre: 'MI CUENTA',
    icon: '👤',
    modulos: ['miPerfil']
  }
];