export interface Permiso {
  id_permiso: number;
  codigo: string;      // 'ver_estudiantes'
  nombre: string;      // 'Ver Estudiantes'
  modulo: string;      // 'academico', 'financiero', 'seguridad', 'personas'
  descripcion?: string;
}