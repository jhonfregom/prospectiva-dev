import { defineStore } from 'pinia';
import axios from 'axios';

export const useGraphicsStore = defineStore('graphics', {
    state: () => ({
        data: [],
        isLoading: false,
        error: null
    }),
    actions: {
        async fetchGraphicsData() {
            this.isLoading = true;
            try {
                const response = await axios.get('/graphics');
                if (response.data.status === 200) {
                    this.data = response.data.data;
                }
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        }
    }
}); 