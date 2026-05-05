import { Injectable, computed } from '@angular/core';
import { AuthService } from './auth.service';
import { Usuario } from '@core/models';
import { MODULES_CONFIG, SECTIONS_CONFIG } from '@core/constants/modules-config';

// Agregar estas interfaces
export interface ModuloData {
  nombre: string;
  icon: string;
  route: string;
  permissions: string[];
}

export interface SeccionVisible {
  nombre: string;
  icon: string;
  modulosVisibles: string[];
  cantidadModulos: number;
}

@Injectable({
  providedIn: 'root'
})
export class PermissionService {
  private currentUser = this.authService.currentUser;

  // Obtener permisos del usuario logueado
  private userPermissions = computed(() => {
    const user = this.currentUser();
    return user?.permisos || [];
  });

  public isAdmin = computed(() => {
    const permissions = this.userPermissions();
    return permissions.includes('*');
  });

  constructor(private authService: AuthService) { }

  // Verificar si el usuario tiene un permiso específico
  hasPermission(permission: string): boolean {
    const permissions = this.userPermissions();
    if (permissions.includes('*')) return true; // Admin
    return permissions.includes(permission);
  }

  // NUEVO: Obtener módulos visibles para el usuario
  getVisibleModules(): string[] {
    const userPerms = this.userPermissions();
    if (userPerms.includes('*')) {
      return Object.keys(MODULES_CONFIG);
    }

    const visibleModules: string[] = [];
    for (const [moduleKey, moduleConfig] of Object.entries(MODULES_CONFIG)) {
      // Si el módulo no requiere permisos (ej: Panel Principal, Mi Perfil)
      if (moduleConfig.permissions.length === 0) {
        visibleModules.push(moduleKey);
      }
      // Si el usuario tiene al menos uno de los permisos requeridos/*  */
      else if (moduleConfig.permissions.some(p => userPerms.includes(p))) {
        visibleModules.push(moduleKey);
      }
    }
    return visibleModules;
  }

  // NUEVO: Obtener secciones con sus módulos visibles (tipado)
  getVisibleSections(): SeccionVisible[] {
    const visibleModules = this.getVisibleModules();
    const result: SeccionVisible[] = [];

    for (const section of SECTIONS_CONFIG) {
      const visibleModsInSection = section.modulos.filter((mod: string) =>
        visibleModules.includes(mod)
      );

      if (visibleModsInSection.length > 0) {
        result.push({
          nombre: section.nombre,
          icon: section.icon,
          modulosVisibles: visibleModsInSection,
          cantidadModulos: visibleModsInSection.length
        });
      }
    }

    return result;
  }

  // NUEVO: Verificar si una sección debe mostrar título
  shouldShowSectionTitle(cantidadModulos: number): boolean {
    return cantidadModulos >= 2;
  }

  // NUEVO: Obtener datos de un módulo específico (TIPADO)
  getModuleData(moduleKey: string): ModuloData | null {
    const module = MODULES_CONFIG[moduleKey as keyof typeof MODULES_CONFIG];
    return module || null;
  }

  hasAnyPermission(permissions: string[]): boolean {
    const userPerms = this.userPermissions();
    if (userPerms.includes('*')) return true;
    return permissions.some(p => userPerms.includes(p));
  }

  hasAllPermissions(permissions: string[]): boolean {
    const userPerms = this.userPermissions();
    if (userPerms.includes('*')) return true;
    return permissions.every(p => userPerms.includes(p));
  }

  getCurrentRol(): string | null {
    return this.currentUser()?.rol_nombre || null;
  }

  hasRole(roleName: string): boolean {
    return this.currentUser()?.rol_nombre === roleName;
  }
}