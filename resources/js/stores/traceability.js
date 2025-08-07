import { defineStore } from "pinia";
import axios from "axios";

export const useTraceabilityStore = defineStore('traceability', {
    state: () => {
        return {
            userTraceability: null,
            availableSections: {
                variables: false,
                matrix: false,
                graphics: false,
                analysis: false,
                hypothesis: false,
                schwartz: false,
                initialconditions: false,
                scenarios: false,
                conclusions: false,
                results: false
            },
            currentRoute: null,
            userRoutes: [],
            isLoading: false,
            error: null
        };
    },

    getters: {
        
        isSectionAvailable: (state) => (section) => {
            if (!state.availableSections) {
                return false;
            }
            return state.availableSections[section] || false;
        },

        getUserRole: () => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            return user.role || 0;
        },

        isAdmin: () => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const isAdmin = user.role === 1;
            return isAdmin;
        },

        getCurrentRoute: (state) => {
            return state.currentRoute;
        },

        getUserRoutes: (state) => {
            return state.userRoutes;
        }
    },

    actions: {
        
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

        async loadCurrentRoute() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/traceability/current-route');
                if (response.data.success) {
                    this.currentRoute = response.data.data;
                }
            } catch (error) {
                console.error('Error loading current route:', error);
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },

        async loadUserRoutes() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/traceability/user-routes');
                if (response.data.success) {
                    this.userRoutes = response.data.data;
                }
            } catch (error) {
                console.error('Error loading user routes:', error);
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },

        async loadAvailableSections() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get('/traceability/available-sections');
                
                if (response.data.success) {
                    this.availableSections = response.data.sections;
                } else {
                    console.error('API no retornó success:', response.data);
                    
                    this.setDefaultSections();
                }
            } catch (error) {
                console.error('Error loading available sections:', error);
                console.error('Error response:', error.response?.data);
                this.error = error.message;

                this.setDefaultSections();
            } finally {
                this.isLoading = false;
            }
        },

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

        async markSectionCompleted(section) {
            
            try {
                
                const response = await axios.post('/traceability/mark-completed', {
                    section: section
                });
                
                if (response.data.success) {
                    
                    await this.loadAvailableSections();
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error marking section as completed:', error);
                console.error('Error response:', error.response?.data);

                this.updateAvailableSections(section);
                return true;
            }
        },

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
                
                const nextSection = sectionOrder[completedIndex + 1];
                if (nextSection) {
                    this.availableSections[nextSection] = true;
                }
            }
        },

        async initialize() {
            
            const userRole = this.getUserRole;
            const isAdmin = this.isAdmin;

            await this.loadAvailableSections();

            await this.loadCurrentRoute();
            await this.loadUserRoutes();
        },

        async forceReloadSections() {
            await this.loadAvailableSections();
        },

        async reverseSectionCompleted(section) {
            try {
                
                const response = await axios.post('/traceability/reverse-section-completed', { section });
                if (!response.data.success) {
                    throw new Error(response.data.message || 'No se pudo bloquear el módulo posterior');
                }
                
                const responseReset = await axios.post('/traceability/reset-edit-locks', { section });
                if (!responseReset.data.success) {
                    throw new Error(responseReset.data.message || 'No se pudo resetear los campos de edición');
                }
                
                await this.forceReloadSections();
            } catch (error) {
                console.error('Error al revertir la sección completada:', error);
            }
        },

        async getCurrentRouteState() {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data.success) {
                    return response.data.state;
                }
                return null;
            } catch (error) {
                console.error('Error getting current route state:', error);
                return null;
            }
        },

        async updateCurrentRouteState(state) {
            try {
                const response = await axios.put('/traceability/current-route-state', { state });
                if (response.data.success) {
                    
                    await this.loadCurrentRoute();
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error updating current route state:', error);
                return false;
            }
        },

        async isSectionClosed(section) {
            try {
                const response = await axios.get(`/traceability/section-closed/${section}`);
                if (response.data.success) {
                    return response.data.closed;
                }
                return false;
            } catch (error) {
                console.error('Error checking if section is closed:', error);
                return false;
            }
        },

        setDefaultSections() {
            const userRole = this.getUserRole;
            
            if (userRole === 1) {
                
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
        }
    }
});