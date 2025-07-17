import { defineStore } from "pinia";
import axios from "axios";

export const useTraceabilityStore = defineStore('traceability', {
    state: () => {
        return {
            userTraceability: null,
            availableSections: null,
            isLoading: false,
            error: null
        };
    },

    getters: {
        // Verifica si una sección está disponible para el usuario
        isSectionAvailable: (state) => (section) => {
            return state.availableSections[section] || false;
        },

        // Obtiene el rol del usuario desde localStorage
        getUserRole: () => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            return user.role || 0;
        },

        // Verifica si el usuario es administrador
        isAdmin: () => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const isAdmin = user.role === 1;
            return isAdmin;
        }
    },

    actions: {
        // Carga el estado de traceability del usuario
        async loadUserTraceability() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/traceability/user');
                if (response.data.success) {
                    this.userTraceability = response.data.data;
                }
            } catch (error) {
                this.error = error.message;
                // Fallback: crear datos locales si la API falla
                this.userTraceability = {
                    variables: '0',
                    matriz: '0',
                    maps: '0',
                    hypothesis: '0',
                    shwartz: '0',
                    conditions: '0',
                    scenarios: '0',
                    conclusions: '0',
                    results: '0'
                };
            } finally {
                this.isLoading = false;
            }
        },

        // Carga las secciones disponibles para el usuario
        async loadAvailableSections() {
            this.isLoading = true;
            this.error = null;
            this.availableSections = null;
            
            try {
                const response = await axios.get('/traceability/available-sections');
                
                if (response.data.success) {
                    this.availableSections = response.data.sections;
                } else {
                    console.error('API no retornó success:', response.data);
                }
            } catch (error) {
                console.error('Error loading available sections:', error);
                console.error('Error response:', error.response?.data);
                this.error = error.message;
                
                // Fallback: configurar permisos basados en rol local
                const userRole = this.getUserRole;
                
                if (userRole === 1) {
                    // Admin: acceso completo
                    this.availableSections = {
                        variables: true,
                        matrix: true,
                        graphics: true,
                        analysis: true,
                        hypothesis: true,
                        schwartz: true,
                        initialconditions: true,
                        scenarios: true,
                        conclusions: true,
                        results: true
                    };
                } else {
                    // Usuario normal: solo variables inicialmente
                    this.availableSections = {
                        variables: true,
                        matrix: false,
                        graphics: false,
                        analysis: false,
                        hypothesis: false,
                        schwartz: false,
                        initialconditions: false,
                        scenarios: false,
                        conclusions: false,
                        results: false
                    };
                }
            } finally {
                this.isLoading = false;
            }
        },

        // Verifica si el usuario puede acceder a una sección específica
        async canAccessSection(section) {
            try {
                const response = await axios.post('/traceability/can-access', {
                    section: section
                });
                return response.data.success && response.data.canAccess;
            } catch (error) {
                console.error('Error checking section access:', error);
                return false;
            }
        },

        // Marca una sección como completada
        async markSectionCompleted(section) {
            
            try {
                // Intentar marcar en el backend
                const response = await axios.post('/traceability/mark-completed', {
                    section: section
                });
                
                if (response.data.success) {
                    // Recargar las secciones disponibles desde la API
                    await this.loadAvailableSections();
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error marking section as completed:', error);
                console.error('Error response:', error.response?.data);
                
                // Si falla la API, actualizar localmente como fallback
                this.updateAvailableSections(section);
                return true;
            }
        },

        // Actualiza las secciones disponibles localmente
        updateAvailableSections(completedSection) {
            
            const sectionOrder = [
                'variables',
                'matrix', 
                'graphics',
                'analysis',
                'hypothesis',
                'schwartz',
                'initialconditions',
                'scenarios',
                'conclusions',
                'results'
            ];

            const completedIndex = sectionOrder.indexOf(completedSection);
            
            if (completedIndex !== -1) {
                // Habilitar la siguiente sección
                const nextSection = sectionOrder[completedIndex + 1];
                if (nextSection) {
                    this.availableSections[nextSection] = true;
                }
            }
        },

        // Inicializa el store
        async initialize() {
            
            const userRole = this.getUserRole;
            const isAdmin = this.isAdmin;
            
            // Cargar secciones disponibles desde la API
            await this.loadAvailableSections();
            
        },

        // Fuerza la recarga de las secciones disponibles
        async forceReloadSections() {
            await this.loadAvailableSections();
        },

        // Revierte la finalización de una sección y bloquea los módulos posteriores
        async reverseSectionCompleted(section) {
            try {
                // Llamar al endpoint para bloquear el módulo posterior
                const response = await axios.post('/traceability/reverse-section-completed', { section });
                if (!response.data.success) {
                    throw new Error(response.data.message || 'No se pudo bloquear el módulo posterior');
                }
                // Llamar al endpoint para resetear los campos de edición de la sección y módulos posteriores
                const responseReset = await axios.post('/traceability/reset-edit-locks', { section });
                if (!responseReset.data.success) {
                    throw new Error(responseReset.data.message || 'No se pudo resetear los campos de edición');
                }
                // Recargar las secciones disponibles
                await this.forceReloadSections();
            } catch (error) {
                console.error('Error al revertir la sección completada:', error);
            }
        }
    }
}); 