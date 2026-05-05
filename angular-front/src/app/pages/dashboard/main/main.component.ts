import { Component, computed, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { AuthService } from '@core/services';

@Component({
  selector: 'app-dashboard-main',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './main.component.html',
  styleUrls: ['./main.component.css']
})
export class MainComponent {
  private authService = inject(AuthService);


  userLogged = computed(() => {
    const user = this.authService.currentUser();
    return user ? `${user.persona.nombres} ${user.persona.apellidos} (${user.rol_nombre})` : '';
  });


  cards = [
    {
      icon: '📖',
      title: 'Consulta académica',
      description: 'Desde aquí puedes consultar las notas de todos los alumnos registrados.',
      buttonText: 'Consultar notas',
      route: '/dashboard/consulta-notas',
      color: 'blue'
    },
    {
      icon: '👩‍🏫',
      title: 'Gestión de profesores',
      description: 'Desde aquí puedes habilitar o restringir el acceso de cada profesor a su módulo de horario.',
      buttonText: 'Profesores',
      route: '/dashboard/profesores',
      color: 'green'
    },
    {
      icon: '👨‍🎓',
      title: 'Gestión de alumnos',
      description: 'Desde aquí puedes crear, modificar y eliminar alumnos, incluyendo su usuario y contraseña.',
      buttonText: 'Alumnos',
      route: '/dashboard/alumnos',
      color: 'purple'
    }
  ];
}