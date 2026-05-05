export interface Persona {
  id_persona: number;
  ci: string;
  nombres: string;
  apellidos: string;
  direccion: string | null;
  telefono: string | null;
  fecha_nac: string; // formato 'YYYY-MM-DD'
}

export interface Usuario {
  id_usuario: number;
  username: string;
  email: string | null;
  id_rol: number;
  rol_nombre: string; // 'Administrador', 'Secretario', 'Docente', 'Tutor'
  id_persona: number;
  persona: Persona;
  permisos: string[]; // ['ver_estudiantes', 'editar_notas', ...]
  estado: number; // 1 = activo, 0 = inactivo
}