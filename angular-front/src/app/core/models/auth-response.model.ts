import { Usuario } from './usuario.model';

export interface AuthResponse {
  success: boolean;
  token: string;
  usuario: Usuario;
}