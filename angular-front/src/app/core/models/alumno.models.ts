export interface Alumno {
  id_estudiante: number;
  rude: string;
  id_persona: number;
  persona: {
    id_persona: number;
    ci: string;
    nombres: string;
    apellidos: string;
    direccion: string;
    telefono: string;
    fecha_nac: string;
    created_at: string;
    updated_at: string;
    estado: number;
  };
}