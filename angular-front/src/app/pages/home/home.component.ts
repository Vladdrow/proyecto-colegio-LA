import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent {
  currentYear = new Date().getFullYear();
  
  features = [
    { icon: '👨‍🎓', title: 'Gestión de Alumnos', description: 'Registro, edición y administración completa de datos estudiantiles.', color: 'blue' },
    { icon: '👩‍🏫', title: 'Gestión de Profesores', description: 'Administra el personal docente, permisos y asignaciones de materias.', color: 'green' },
    { icon: '📅', title: 'Horarios y Turnos', description: 'Organización de horarios por profesor, curso y paralelo.', color: 'purple' },
    { icon: '📊', title: 'Calificaciones', description: 'Consulta de notas por trimestre con evaluación integral.', color: 'orange' },
    { icon: '📋', title: 'Bitácora de Accesos', description: 'Registro completo de actividad y accesos al sistema.', color: 'pink' },
    { icon: '⚙️', title: 'Configuración', description: 'Gestiones académicas, niveles y configuración general del sistema.', color: 'teal' }
  ];

  roles = [
    { emoji: '🔧', title: 'Administrador', description: 'Control total del sistema' },
    { emoji: '👨‍🏫', title: 'Profesor', description: 'Horarios y asignaturas' },
    { emoji: '👪', title: 'Apoderado', description: 'Consulta de notas' }
  ];
}