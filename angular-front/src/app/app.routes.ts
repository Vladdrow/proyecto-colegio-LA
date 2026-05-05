import { Routes } from '@angular/router';
import { AuthGuard, PermissionGuard } from '@core/guards';
import { HomeComponent } from './pages/home/home.component';
import { LoginComponent } from './pages/login/login.component';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },

  // Dashboard con layout
  {
    path: 'dashboard',
    loadComponent: () => import('./layouts/dashboard/dashboard-layout.component')
      .then(m => m.DashboardLayoutComponent),
    canActivate: [AuthGuard],  // Proteger todo el dashboard
    children: [
      { path: '', redirectTo: 'main', pathMatch: 'full' },

      // Rutas con permiso requerido
      { path: 'main', loadComponent: () => import('./pages/dashboard/main/main.component').then(m => m.MainComponent) },
      { path: 'academico/alumnos', loadComponent: () => import('./pages/dashboard/alumnos/alumnos.component').then(m => m.AlumnosComponent), canActivate: [PermissionGuard], data: { permission: 'ver_estudiantes' } },
      {
        path: 'academico/alumnos/nuevo',
        loadComponent: () => import('./pages/dashboard/alumnos/alumno-form.component')
          .then(m => m.AlumnoFormComponent),
        canActivate: [AuthGuard, PermissionGuard],
        data: { permission: 'crear_estudiante' }
      }
      /*  // Seguridad
       { path: 'seguridad/bitacora', loadComponent: () => import('./pages/dashboard/seguridad/bitacora/bitacora.component').then(m => m.BitacoraComponent), canActivate: [PermissionGuard], data: { permission: 'ver_bitacora' } },
       { path: 'seguridad/roles-permisos', loadComponent: () => import('./pages/dashboard/seguridad/roles-permisos/roles-permisos.component').then(m => m.RolesPermisosComponent), canActivate: [PermissionGuard], data: { permission: 'asignar_permisos' } },
       
       // Personas
       { path: 'usuarios/administradores', loadComponent: () => import('./pages/dashboard/usuarios/administradores/administradores.component').then(m => m.AdministradoresComponent), canActivate: [PermissionGuard], data: { permission: 'ver_usuarios' } },
       { path: 'usuarios/profesores', loadComponent: () => import('./pages/dashboard/usuarios/profesores/profesores.component').then(m => m.ProfesoresComponent), canActivate: [PermissionGuard], data: { permission: 'ver_docentes' } },
       
       // Académico
       
       { path: 'academico/horarios', loadComponent: () => import('./pages/dashboard/academico/horarios/horarios.component').then(m => m.HorariosComponent), canActivate: [PermissionGuard], data: { permission: 'ver_horarios' } },
       { path: 'academico/calificaciones', loadComponent: () => import('./pages/dashboard/academico/calificaciones/calificaciones.component').then(m => m.CalificacionesComponent), canActivate: [PermissionGuard], data: { permission: 'ver_calificaciones' } },
       
       // Financiero
       { path: 'financiero/pagos', loadComponent: () => import('./pages/dashboard/financiero/pagos/pagos.component').then(m => m.PagosComponent), canActivate: [PermissionGuard], data: { permission: 'ver_pagos' } },
       
       // Mi cuenta (sin permiso específico)
       { path: 'perfil', loadComponent: () => import('./pages/dashboard/perfil/perfil.component').then(m => m.PerfilComponent) } */
    ]
  },

  { path: '**', redirectTo: '' }
];