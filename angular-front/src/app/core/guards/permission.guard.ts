import { Injectable, inject } from '@angular/core';
import { CanActivate, Router, UrlTree, ActivatedRouteSnapshot } from '@angular/router';
import { PermissionService } from '../services/permission.service';

@Injectable({
  providedIn: 'root'
})
export class PermissionGuard implements CanActivate {
  // Usar inject() en lugar de constructor
  private permissionService = inject(PermissionService);
  private router = inject(Router);

  canActivate(route: ActivatedRouteSnapshot): boolean | UrlTree {
    // Obtener el permiso requerido desde los datos de la ruta
    const requiredPermission = route.data['permission'] as string;
    
    // Si no se requiere permiso específico, permitir acceso
    if (!requiredPermission) {
      return true;
    }
    
    // Verificar si el usuario tiene el permiso
    if (this.permissionService.hasPermission(requiredPermission)) {
      return true;
    }
    
    // Redirigir al dashboard principal si no tiene permiso
    console.warn(`Acceso denegado: se requiere permiso "${requiredPermission}"`);
    return this.router.createUrlTree(['/dashboard/main']);
  }
}