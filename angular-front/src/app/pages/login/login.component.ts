import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  private authService = inject(AuthService);
  private router = inject(Router);
  private fb = inject(FormBuilder);

  loginForm: FormGroup;
  isLoading = false;
  errorMessage = '';

  constructor() {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      rememberMe: [false]
    });
  }

  onSubmit() {
    if (this.loginForm.valid) {
      this.isLoading = true;
      this.errorMessage = '';

      const { username, password, rememberMe } = this.loginForm.value;

      this.authService.login(username, password).subscribe({
        next: (response) => {  // ← RECIBIMOS el response
          console.log('Login exitoso:', response);

          // Los datos del usuario ya están guardados en AuthService
          // Pero puedes acceder a ellos si los necesitas:
          const usuario = response.usuario;
          const token = response.token;

          console.log('Usuario logueado:', usuario.persona.nombres, usuario.persona.apellidos);
          console.log('Rol:', usuario.rol_nombre);
          console.log('Permisos:', usuario.permisos);

          // Redirigir al dashboard
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          this.isLoading = false;
          if (error.status === 401) {
            this.errorMessage = 'Usuario o contraseña incorrectos';
          } else {
            this.errorMessage = 'Error al conectar con el servidor';
          }
        }
      });
    } else {
      this.markFormGroupTouched(this.loginForm);
    }
  }

  // Método para marcar todos los campos como tocados y mostrar errores
  private markFormGroupTouched(formGroup: FormGroup) {
    Object.values(formGroup.controls).forEach(control => {
      control.markAsTouched();
      if (control instanceof FormGroup) {
        this.markFormGroupTouched(control);
      }
    });
  }

  // Getters para fácil acceso en el template
  get username() { return this.loginForm.get('username'); }
  get password() { return this.loginForm.get('password'); }

  // Mock para pruebas (opcional)
  loginMock(rol: string) {
    this.authService.loginMock(rol);
    this.router.navigate(['/dashboard']);
  }
}