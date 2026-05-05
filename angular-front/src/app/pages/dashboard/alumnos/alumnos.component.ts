import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '@core/services/auth.service';
import { AlumnoFormComponent } from './alumno-form.component';

interface Alumno {
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
        genero: string;
    };
}

interface ApiResponse {
    success: boolean;
    data: {
        current_page: number;
        data: Alumno[];
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        links: any[];
        next_page_url: string | null;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number;
        total: number;
    };
    filters: any[];
}

@Component({
    selector: 'app-alumnos',
    standalone: true,
    imports: [CommonModule, RouterLink, AlumnoFormComponent, FormsModule],
    templateUrl: './alumnos.component.html',
    styleUrls: ['./alumnos.component.css']
})
export class AlumnosComponent implements OnInit {
    private http = inject(HttpClient);
    private authService = inject(AuthService);

    // ============================================
    // ESTADO DE DATOS
    // ============================================
    alumnos = signal<Alumno[]>([]);
    isLoading = signal(true);
    errorMessage = signal('');

    // ============================================
    // PAGINACIÓN
    // ============================================
    currentPage = signal(1);
    totalPages = signal(1);
    totalItems = signal(0);
    perPage = signal(15);

    // ============================================
    // PERMISOS
    // ============================================
    canEdit = signal(false);
    canDelete = signal(false);
    canCreate = signal(false);

    // ============================================
    // FILTROS
    // ============================================
    searchTerm = signal('');
    filterGenero = signal('');
    filterEstado = signal('');
    showFilters = signal(false);

    // ============================================
    // MODALES
    // ============================================
    showModal = signal(false);           // Modal nuevo alumno
    showEditModal = signal(false);       // Modal editar alumno
    alumnoToEdit = signal<any>(null);    // Datos del alumno a editar
    showDeleteModal = signal(false);     // Modal confirmar desactivación
    //alumnoToDelete = signal<{ id: number; nombre: string } | null>(null);
    alumnoToDelete = signal<{ id: number; nombre: string; esActivo: boolean } | null>(null);
    isDeleting = signal(false);

    private apiUrl = 'http://localhost:8081/api';

    // ============================================
    // LIFECYCLE HOOKS
    // ============================================
    ngOnInit(): void {
        this.checkPermissions();
        this.loadAlumnos();
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    min(a: number, b: number): number {
        return Math.min(a, b);
    }

    getNombreCompleto(alumno: Alumno): string {
        return `${alumno.persona.nombres} ${alumno.persona.apellidos}`;
    }

    getCi(alumno: Alumno): string {
        return alumno.persona.ci;
    }

    getFechaNac(alumno: Alumno): string {
        return alumno.persona.fecha_nac;
    }

    getGenero(alumno: Alumno): string {
        return alumno.persona.genero;
    }

    getEstado(alumno: Alumno): string {
        return alumno.persona.estado === 1 ? 'Activo' : 'Inactivo';
    }

    getPaginationPages(): number[] {
        const total = this.totalPages();
        const current = this.currentPage();
        const pages: number[] = [];
        const start = Math.max(1, current - 2);
        const end = Math.min(total, current + 2);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    }

    // ============================================
    // PERMISOS
    // ============================================
    checkPermissions(): void {
        const user = this.authService.getCurrentUser();
        const permisos = user?.permisos || [];

        this.canEdit.set(permisos.includes('editar_estudiante'));
        this.canDelete.set(permisos.includes('eliminar_estudiante'));
        this.canCreate.set(permisos.includes('crear_estudiante'));
    }

    // ============================================
    // CRUD - CARGAR ALUMNOS
    // ============================================
    loadAlumnos(): void {
        this.isLoading.set(true);

        const params: any = {
            page: this.currentPage().toString(),
            per_page: this.perPage().toString()
        };

        if (this.searchTerm()) params.search = this.searchTerm();
        if (this.filterGenero()) params.genero = this.filterGenero();
        if (this.filterEstado()) params.estado = this.filterEstado();

        this.http.get<ApiResponse>(`${this.apiUrl}/estudiantes`, { params })
            .subscribe({
                next: (response) => {
                    if (response.success && response.data && Array.isArray(response.data.data)) {
                        this.alumnos.set(response.data.data);
                        this.totalItems.set(response.data.total || 0);
                        this.totalPages.set(response.data.last_page || 1);
                        this.currentPage.set(response.data.current_page || 1);
                        this.errorMessage.set('');
                    } else {
                        this.alumnos.set([]);
                    }
                    this.isLoading.set(false);
                },
                error: (error) => {
                    console.error('Error al cargar alumnos:', error);
                    this.errorMessage.set('Error al cargar el listado de alumnos');
                    this.isLoading.set(false);
                }
            });
    }

    // ============================================
    // FILTROS
    // ============================================
    onSearchChange(term: string): void {
        this.searchTerm.set(term);
        this.currentPage.set(1);
        this.loadAlumnos();
    }

    onGeneroChange(genero: string): void {
        this.filterGenero.set(genero);
        this.currentPage.set(1);
        this.loadAlumnos();
    }

    onEstadoChange(estado: string): void {
        this.filterEstado.set(estado);
        this.currentPage.set(1);
        this.loadAlumnos();
    }

    limpiarFiltros(): void {
        this.searchTerm.set('');
        this.filterGenero.set('');
        this.filterEstado.set('');
        this.currentPage.set(1);
        this.loadAlumnos();
    }

    toggleFilters(): void {
        this.showFilters.update(v => !v);
    }

    // ============================================
    // PAGINACIÓN
    // ============================================
    goToPage(page: number): void {
        if (page < 1 || page > this.totalPages()) return;
        this.currentPage.set(page);
        this.loadAlumnos();
    }

    // ============================================
    // MODAL - CREAR ALUMNO
    // ============================================
    openModal(): void {
        this.showModal.set(true);
        setTimeout(() => {
            const overlay = document.querySelector('.modal-overlay');
            const modal = document.querySelector('.modal-container');
            if (overlay && modal) {
                overlay.scrollIntoView({ behavior: 'instant', block: 'center' });
            }
        }, 0);
    }

    closeModal(): void {
        this.showModal.set(false);
    }

    onAlumnoCreado(): void {
        this.loadAlumnos();
        this.closeModal();
    }

    // ============================================
    // MODAL - EDITAR ALUMNO
    // ============================================
    openEditModal(alumno: Alumno): void {
        this.alumnoToEdit.set(alumno);
        this.showEditModal.set(true);
    }

    closeEditModal(): void {
        this.showEditModal.set(false);
        this.alumnoToEdit.set(null);
    }

    onAlumnoActualizado(): void {
        this.loadAlumnos();
        this.closeEditModal();
    }

    // ============================================
    // MODAL - DESACTIVAR ALUMNO
    // ============================================
    confirmDelete(alumno: Alumno): void {
        this.alumnoToDelete.set({
            id: alumno.id_estudiante,
            nombre: this.getNombreCompleto(alumno),
            esActivo: alumno.persona.estado === 1
        });
        this.showDeleteModal.set(true);
    }

    confirmReactivate(alumno: Alumno): void {
        this.alumnoToDelete.set({
            id: alumno.id_estudiante,
            nombre: this.getNombreCompleto(alumno),
            esActivo: false
        });
        this.showDeleteModal.set(true);
    }

    closeDeleteModal(): void {
        this.showDeleteModal.set(false);
        this.alumnoToDelete.set(null);
        this.isDeleting.set(false);
    }

    executeStatusChange(): void {
        const alumno = this.alumnoToDelete();
        if (!alumno) return;

        this.isDeleting.set(true);

        const nuevoEstado = alumno.esActivo ? 0 : 1;

        this.http.put(`${this.apiUrl}/estudiantes/${alumno.id}/estado`, { estado: nuevoEstado })
            .subscribe({
                next: (response: any) => {
                    const mensaje = alumno.esActivo
                        ? 'Alumno desactivado correctamente'
                        : 'Alumno reactivado correctamente';

                    alert(mensaje);
                    this.loadAlumnos();
                    this.closeDeleteModal();
                },
                error: (error) => {
                    console.error('Error al cambiar estado:', error);
                    const errorMsg = error.error?.message || 'Error al cambiar el estado del alumno';
                    alert(errorMsg);
                    this.isDeleting.set(false);
                    this.closeDeleteModal();
                }
            });
    }
}