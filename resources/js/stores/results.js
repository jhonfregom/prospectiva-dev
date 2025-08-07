import { defineStore } from 'pinia';
import axios from 'axios';

export const useResultsStore = defineStore('results', {
    state: () => ({
        users: [],
        isLoading: false,
        error: null
    }),
    actions: {
        async fetchUsers() {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await axios.get('/results/users');
                if (response.data.status === 200) {
                    this.users = response.data.data;
                }
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
            } finally {
                this.isLoading = false;
            }
        },
        
        async fetchUsersByRoute(routeId) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await axios.get(`/results/users-by-route?route_id=${routeId}`);
                if (response.data.status === 200) {
                    this.users = response.data.data;
                }
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
            } finally {
                this.isLoading = false;
            }
        }
    }
});