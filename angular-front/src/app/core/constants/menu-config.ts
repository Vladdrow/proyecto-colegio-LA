export interface MenuItem {
  path: string;
  title: string;
  permission?: string;
  icon?: string;
}

export interface MenuGroup {
  group: string;
  icon: string;
  items: MenuItem[];
}

export const MENU_CONFIG: MenuGroup[] = [
  {
    group: "PRINCIPAL",
    icon: "🏠",
    items: [
      { path: "/dashboard/main", title: "Panel Principal", icon: "📊" }
    ]
  },
  {
    group: "USUARIOS",
    icon: "👥",
    items: [
      { path: "/dashboard/usuarios/administradores", title: "Administradores", permission: "ver_usuarios", icon: "👨‍💼" },
      { path: "/dashboard/usuarios/profesores", title: "Profesores", permission: "ver_docentes", icon: "👩‍🏫" },
      { path: "/dashboard/usuarios/tutores", title: "Tutores", permission: "ver_tutores", icon: "👪" },
      { path: "/dashboard/usuarios/personal-administrativo", title: "Personal Adm.", permission: "ver_usuarios", icon: "📋" }
    ]
  },
  {
    group: "ACADÉMICO",
    icon: "📚",
    items: [
      { path: "/dashboard/academico/alumnos", title: "Alumnos", permission: "ver_estudiantes", icon: "👨‍🎓" },
      { path: "/dashboard/academico/niveles", title: "Niveles", permission: "ver_cursos", icon: "📚" },
      { path: "/dashboard/academico/materias", title: "Materias", permission: "ver_materias", icon: "📖" },
      { path: "/dashboard/academico/horarios", title: "Horarios", permission: "ver_horarios", icon: "📅" },
      { path: "/dashboard/academico/calificaciones", title: "Calificaciones", permission: "ver_calificaciones", icon: "📊" }
    ]
  },
  {
    group: "FINANCIERO",
    icon: "💰",
    items: [
      { path: "/dashboard/financiero/pagos", title: "Pagos", permission: "ver_pagos", icon: "💵" },
      { path: "/dashboard/financiero/becas", title: "Becas", permission: "ver_becas", icon: "🎓" }
    ]
  },
  {
    group: "SEGURIDAD",
    icon: "🔐",
    items: [
      { path: "/dashboard/seguridad/bitacora", title: "Bitácora", permission: "ver_bitacora", icon: "📋" },
      { path: "/dashboard/seguridad/roles-permisos", title: "Permisos", permission: "asignar_permisos", icon: "🎚️" }
    ]
  },
  {
    group: "MI CUENTA",
    icon: "👤",
    items: [
      { path: "/dashboard/perfil", title: "Mi Perfil", icon: "👤" }
    ]
  }
];