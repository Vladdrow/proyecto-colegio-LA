import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { catchError, tap, throwError, Observable, of, timeout } from 'rxjs';
import { Usuario, AuthResponse } from '@core/models';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);

  private apiUrl = 'http://localhost:8081/api';

  private currentUserSignal = signal<Usuario | null>(null);
  public currentUser = this.currentUserSignal.asReadonly();

  constructor() {
    this.loadSession();
  }

  /**
   * Login real con backend
   */
  login(identifier: string, password: string) {
    return this.http.post<AuthResponse>(`${this.apiUrl}/login`, { identifier, password })
      .pipe(
        tap(response => {
          if (response.success) {
            this.setSession(response);
          }
        }),
        catchError(error => {
          console.error('Login error:', error);
          return throwError(() => error);
        })
      );
  }

  /**
   * Cerrar sesión - Mejorado
   */
  logout(): Observable<any> {
    const token = localStorage.getItem('token');

    // Si no hay token, limpiar sesión local inmediatamente
    if (!token) {
      this.clearSession();
      return of(void 0);
    }

    // Intentar cerrar sesión en el backend
    return this.http.post(`${this.apiUrl}/logout`, {}, { timeout: 15000 }).pipe(
      tap(() => {
        console.log('Sesión cerrada correctamente');
        this.clearSession();
      }),
      catchError((error) => {
        // En caso de error (red, timeout, etc.), igual limpiamos sesión local
        console.warn('Error en logout backend, limpiando sesión local:', error.message || error);
        this.clearSession();
        return of(void 0); // Retornamos éxito para no romper el flujo
      })
    );
  }

  /**
   * Obtener usuario actual (para mantener sesión al recargar)
   */
  getCurrentUserFromBackend() {
    const token = localStorage.getItem('token');
    if (!token) {
      return;
    }

    this.http.get<{ success: boolean; usuario: Usuario }>(`${this.apiUrl}/user`)
      .subscribe({
        next: (response) => {
          if (response.success) {
            this.currentUserSignal.set(response.usuario);
          } else {
            this.clearSession();
          }
        },
        error: () => {
          this.clearSession();
        }
      });
  }

  /**
   * Guardar sesión
   */
  private setSession(response: AuthResponse): void {
    console.log('✅ setSession() guardando token');
    localStorage.setItem('token', response.token);
    localStorage.setItem('usuario', JSON.stringify(response.usuario));
    this.currentUserSignal.set(response.usuario);
  }

  /**
   * Limpiar sesión local - Seguro
   */
  private clearSession(): void {  
    console.trace('⚠️ clearSession() fue llamado - Rastreo de origen');
    try {
      localStorage.removeItem('token');
      localStorage.removeItem('usuario');
      this.currentUserSignal.set(null);
    } catch (error) {
      console.error('Error al limpiar sesión local:', error);
    }
  }

  /** POSIBLEMENTE USAR A FUTURO
   * Cargar sesión desde localStorage (al iniciar)
   */
  /* private loadSession(): void {
    const token = localStorage.getItem('token');
    const usuarioStr = localStorage.getItem('usuario');

    console.log('loadSession - token existe?', !!token);
    console.log('loadSession - usuarioStr existe?', !!usuarioStr);

    if (token && usuarioStr) {
      try {
        const usuario = JSON.parse(usuarioStr);
        this.currentUserSignal.set(usuario);
        console.log('✅ Sesión restaurada');
        this.getCurrentUserFromBackend();
      } catch {
        this.clearSession();
      }
    }
  } */
  
  private loadSession(): void {
    const token = localStorage.getItem('token');
    const usuarioStr = localStorage.getItem('usuario');

    console.log('loadSession - token existe?', !!token);
    console.log('loadSession - usuarioStr existe?', !!usuarioStr);

    if (token && usuarioStr) {
      try {
        const usuario = JSON.parse(usuarioStr);
        this.currentUserSignal.set(usuario);
        console.log('✅ Sesión restaurada');
      } catch (e) {
        console.error('❌ Error al parsear usuario:', e);
        this.clearSession();
      }
    }
  }

  /**
   * Obtener usuario actual (síncrono)
   */
  getCurrentUser(): Usuario | null {
    return this.currentUserSignal();
  }

  /**
   * Verificar autenticación
   */
  isAuthenticated(): boolean {
    return this.currentUserSignal() !== null && localStorage.getItem('token') !== null;
  }

  /**
   * Login mock (para pruebas)
   */
  loginMock(rol: string): boolean {
    const MOCK_USERS: Record<string, any> = {
      admin: {
        id_usuario: 1,
        username: 'admin',
        email: 'admin@colegio.com',
        id_rol: 3,
        rol_nombre: 'Administrador',
        id_persona: 1,
        persona: {
          id_persona: 1,
          ci: '12345678',
          nombres: 'Admin',
          apellidos: 'Principal',
          direccion: 'Av. Principal 123',
          telefono: '77712345',
          fecha_nac: '1980-01-01'
        },
        permisos: ['*'],
        estado: 1
      },
      secretario: {
        id_usuario: 2,
        username: 'secretaria',
        email: 'secretaria@colegio.com',
        id_rol: 2,
        rol_nombre: 'Secretario',
        id_persona: 2,
        persona: {
          id_persona: 2,
          ci: '87654321',
          nombres: 'María',
          apellidos: 'González',
          direccion: 'Calle secundaria 456',
          telefono: '77754321',
          fecha_nac: '1985-05-10'
        },
        permisos: ['ver_estudiantes', 'crear_estudiante'],
        estado: 1
      },
      docente: {
        id_usuario: 3,
        username: 'docente',
        email: 'docente@colegio.com',
        id_rol: 6,
        rol_nombre: 'Docente',
        id_persona: 3,
        persona: {
          id_persona: 3,
          ci: '11223344',
          nombres: 'Carlos',
          apellidos: 'Ramírez',
          direccion: 'Av. Universidad 789',
          telefono: '77798765',
          fecha_nac: '1990-08-20'
        },
        permisos: ['ver_estudiantes', 'registrar_notas'],
        estado: 1
      },
      tutor: {
        id_usuario: 4,
        username: 'tutor',
        email: 'tutor@colegio.com',
        id_rol: 5,
        rol_nombre: 'Tutor',
        id_persona: 4,
        persona: {
          id_persona: 4,
          ci: '44332211',
          nombres: 'Ana',
          apellidos: 'López',
          direccion: 'Barrio Las Palmas 321',
          telefono: '77744332',
          fecha_nac: '1975-03-15'
        },
        permisos: ['ver_estudiantes', 'ver_notas'],
        estado: 1
      }
    };

    const user = MOCK_USERS[rol];
    if (user) {
      this.setSession({ success: true, token: 'mock-token', usuario: user });
      return true;
    }
    return false;
  }
}