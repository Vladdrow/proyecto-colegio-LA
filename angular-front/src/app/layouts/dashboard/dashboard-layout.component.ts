import { Component, computed, inject, signal, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { PermissionService, SeccionVisible } from '@core/services/permission.service';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-dashboard-layout',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive, RouterOutlet],
  templateUrl: './dashboard-layout.component.html',
  styleUrls: ['./dashboard-layout.component.css']
})
export class DashboardLayoutComponent {
  sidebarCollapsed = signal(false);
  mobileMenuOpen = signal(false);
  isMobile = signal(false);
  isLoggingOut = signal(false);  // ← Nuevo: estado de cierre de sesión

  expandedSections = signal<Record<string, boolean>>({});

  private permissionService = inject(PermissionService);
  private authService = inject(AuthService);
  private router = inject(Router);

  visibleSections = computed<SeccionVisible[]>(() => {
    return this.permissionService.getVisibleSections();
  });

  constructor() {
    this.checkScreenSize();
    window.addEventListener('resize', () => this.checkScreenSize());

    effect(() => {
      const sections = this.visibleSections();
      const currentExpanded = this.expandedSections();
      let changed = false;

      for (const section of sections) {
        if (currentExpanded[section.nombre] === undefined) {
          currentExpanded[section.nombre] = false;
          changed = true;
        }
      }

      if (changed) {
        this.expandedSections.set({ ...currentExpanded });
      }
    });
  }

  checkScreenSize() {
    this.isMobile.set(window.innerWidth <= 768);
    if (!this.isMobile()) {
      this.mobileMenuOpen.set(false);
    }
  }

  toggleSidebar() {
    if (this.isMobile()) {
      this.mobileMenuOpen.update(v => !v);
    } else {
      this.sidebarCollapsed.update(v => !v);
    }
  }

  toggleSection(sectionName: string) {
    if (this.sidebarCollapsed() && !this.isMobile()) return;
    this.expandedSections.update(prev => ({
      ...prev,
      [sectionName]: !prev[sectionName]
    }));
  }

  isSectionExpanded(sectionName: string): boolean {
    return this.expandedSections()[sectionName] ?? true;
  }

  closeMobileMenu() {
    if (this.isMobile()) {
      this.mobileMenuOpen.set(false);
    }
  }

  expandSidebar() {
    if (!this.isMobile() && this.sidebarCollapsed()) {
      this.sidebarCollapsed.set(false);
    }
  }

  logout() {
    if (this.isLoggingOut()) return;  // ← Evita múltiples clics
    
    this.isLoggingOut.set(true);
    
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/']);
      },
      error: () => {
        this.isLoggingOut.set(false);
        // Si hay error, igual redirigimos
        this.router.navigate(['/']);
      }
    });
  }

  getModuleData(moduleKey: string) {
    return this.permissionService.getModuleData(moduleKey);
  }
}