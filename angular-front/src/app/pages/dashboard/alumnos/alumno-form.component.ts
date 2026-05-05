import { Component, inject, input, output, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';

@Component({
    selector: 'app-alumno-form',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule],
    templateUrl: './alumno-form.component.html',
    styleUrls: ['./alumno-form.component.css']
})
export class AlumnoFormComponent implements OnInit {
    private fb = inject(FormBuilder);
    private http = inject(HttpClient);

    // Inputs para controlar el modo
    modoEdicion = input(false);
    alumnoData = input<any>(null);

    // Eventos
    close = output<void>();
    alumnoGuardado = output<any>();

    private apiUrl = 'http://localhost:8081/api';

    isLoading = false;
    errorMessage = '';
    successMessage = '';

    alumnoForm: FormGroup = this.fb.group({
        ci: ['', [Validators.required, Validators.minLength(4), Validators.maxLength(15)]],
        nombres: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(100)]],
        apellidos: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(100)]],
        direccion: ['', [Validators.maxLength(200)]],
        telefono: ['', [Validators.pattern(/^[0-9]{7,15}$/)]],
        fecha_nac: ['', [Validators.required]],
        genero: ['', [Validators.required]],
        rude: ['', [Validators.required, Validators.minLength(5), Validators.maxLength(50)]]
    });

    generos = [
        { value: 'M', label: 'Masculino' },
        { value: 'F', label: 'Femenino' },
        { value: 'O', label: 'Otro' }
    ];

    ngOnInit(): void {
        if (this.modoEdicion() && this.alumnoData()) {
            this.cargarDatosEnFormulario();
        }
    }

    cargarDatosEnFormulario(): void {
        const data = this.alumnoData();
        if (!data) return;

        this.alumnoForm.patchValue({
            ci: data.persona?.ci || '',
            nombres: data.persona?.nombres || '',
            apellidos: data.persona?.apellidos || '',
            direccion: data.persona?.direccion || '',
            telefono: data.persona?.telefono || '',
            fecha_nac: data.persona?.fecha_nac || '',
            genero: data.persona?.genero || '',
            rude: data.rude || ''
        });
    }

    onSubmit(): void {
        if (this.alumnoForm.invalid) {
            this.markFormGroupTouched(this.alumnoForm);
            return;
        }

        this.isLoading = true;
        this.errorMessage = '';
        this.successMessage = '';

        const formData = this.alumnoForm.value;

        let request;

        if (this.modoEdicion()) {
            // Modo edición: PUT /estudiantes/{id}
            const id = this.alumnoData()?.id_estudiante;
            request = this.http.put(`${this.apiUrl}/estudiantes/${id}`, formData);
        } else {
            // Modo creación: POST /estudiantes
            request = this.http.post(`${this.apiUrl}/estudiantes`, formData);
        }

        request.subscribe({
            next: (response: any) => {
                const mensaje = this.modoEdicion()
                    ? 'Alumno actualizado correctamente'
                    : 'Alumno registrado correctamente';

                this.successMessage = mensaje;
                this.isLoading = false;
                this.alumnoGuardado.emit(response);

                setTimeout(() => this.closeModal(), 1500);
            },
            error: (error) => {
                this.isLoading = false;

                if (error.status === 422) {
                    const errors = error.error?.errors;
                    this.errorMessage = errors
                        ? Object.values(errors).flat().join(', ')
                        : 'Datos inválidos. Verifique los campos.';
                } else if (error.status === 409) {
                    this.errorMessage = 'Ya existe un alumno con ese CI o RUDE';
                } else {
                    this.errorMessage = 'Error al conectar con el servidor';
                }
            }
        });
    }

    closeModal(): void {
        this.close.emit();
    }

    private markFormGroupTouched(formGroup: FormGroup): void {
        Object.values(formGroup.controls).forEach(control => {
            control.markAsTouched();
            if (control instanceof FormGroup) this.markFormGroupTouched(control);
        });
    }

    // Getters
    get ci() { return this.alumnoForm.get('ci'); }
    get nombres() { return this.alumnoForm.get('nombres'); }
    get apellidos() { return this.alumnoForm.get('apellidos'); }
    get direccion() { return this.alumnoForm.get('direccion'); }
    get telefono() { return this.alumnoForm.get('telefono'); }
    get fecha_nac() { return this.alumnoForm.get('fecha_nac'); }
    get genero() { return this.alumnoForm.get('genero'); }
    get rude() { return this.alumnoForm.get('rude'); }
}