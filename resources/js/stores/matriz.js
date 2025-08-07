import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useMatrizStore = defineStore('matriz', () => {
    
    const variables = ref([]);
    const isLoading = ref(false);
    const matrizData = ref({});
    const isLocked = ref(false); 

    const getMatrizData = computed(() => matrizData.value);

    async function fetchMatrizData() {
        isLoading.value = true;
        try {
            
            const variablesResponse = await axios.get('/variables');
            if (variablesResponse.data && variablesResponse.data.data) {
                variables.value = variablesResponse.data.data;
            }

            const matrizResponse = await axios.get('/matriz');
            if (matrizResponse.data) {
                isLocked.value = matrizResponse.data.state == 1;

                const newMatrizData = {};
                if (matrizResponse.data.matriz) {
                    matrizResponse.data.matriz.forEach(item => {
                        const key = `${item.id_variable}-${item.id_resp_depen}`;
                        newMatrizData[key] = item.id_resp_influ;
                    });
                }

                variables.value.forEach(varOrigen => {
                    variables.value.forEach(varDestino => {
                        if (varOrigen.id !== varDestino.id) {
                            const key = `${varOrigen.id}-${varDestino.id}`;
                            if (newMatrizData[key] === undefined) {
                                newMatrizData[key] = 0;
                            }
                        }
                    });
                });
                matrizData.value = newMatrizData;
            }

        } catch (error) {
            console.error('Error al cargar los datos de la matriz:', error);
        } finally {
            isLoading.value = false;
        }
    }

    function updateMatrizValue(origenId, destinoId, valor) {
        if (isLocked.value) return; 
        const key = `${origenId}-${destinoId}`;
        matrizData.value[key] = valor;
    }

    async function saveMatriz(textsStore = null) {
        if (isLocked.value) {
            return { success: false, message: 'La matriz ya está guardada y no se puede modificar.' };
        }
        isLoading.value = true;
        try {
            const matrizPayload = [];

            for (const key in matrizData.value) {
                if (Object.hasOwnProperty.call(matrizData.value, key)) {
                    const value = matrizData.value[key];
                    const [origenId, destinoId] = key.split('-').map(Number);

                    if (isNaN(origenId) || isNaN(destinoId) || value === null || value === undefined) {
                        console.warn(`Omitiendo dato inválido para la clave: ${key}`);
                        continue;
                    }

                    matrizPayload.push({
                        
                        id_variable: origenId,
                        id_resp_depen: destinoId,
                        id_resp_influ: value
                    });
                }
            }

            if (matrizPayload.length === 0) {
                return { success: true, message: 'No hay datos nuevos para guardar.' };
            }

            const response = await axios.post('/matriz', { matriz: matrizPayload });

            isLocked.value = true;

            const successMessage = textsStore ? textsStore.getText('matriz.save_success') : 'Matriz guardada correctamente.';
            return { success: true, message: response.data.message || successMessage };
        } catch (error) {
            console.error('Error al guardar la matriz:', error.response?.data || error.message);
            const errorMessage = textsStore ? textsStore.getText('matriz.save_error') : 'Error al guardar la matriz.';
            const message = error.response?.data?.message || errorMessage;
            return { success: false, message: message };
        } finally {
            isLoading.value = false;
        }
    }

    return {
        variables,
        isLoading,
        isLocked, 
        matrizData,
        getMatrizData,
        fetchMatrizData,
        updateMatrizValue,
        saveMatriz
    };
});