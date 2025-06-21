import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useMatrizStore = defineStore('matriz', () => {
    // Estado
    const variables = ref([]);
    const isLoading = ref(false);
    const matrizData = ref({});

    // Computed
    const getMatrizData = computed(() => matrizData.value);

    // Métodos
    async function fetchMatrizData() {
        isLoading.value = true;
        try {
            const variablesResponse = await axios.get('/variables');
            variables.value = variablesResponse.data.data;

            const matrizResponse = await axios.get('/matriz');
            if (matrizResponse.data && matrizResponse.data.matriz) {
                const newMatrizData = {};
                matrizResponse.data.matriz.forEach(item => {
                    const key = `${item.id_variable}-${item.id_resp_depen}`;
                    newMatrizData[key] = item.id_resp_influ;
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
        const key = `${origenId}-${destinoId}`;
        matrizData.value[key] = valor;
    }

    async function saveMatriz() {
        isLoading.value = true;
        try {
            const matrizPayload = [];
            let idCounter = 1; // Contador para id_matriz

            for (const key in matrizData.value) {
                if (Object.hasOwnProperty.call(matrizData.value, key)) {
                    const value = matrizData.value[key];
                    const [origenId, destinoId] = key.split('-').map(Number);

                    if (isNaN(origenId) || isNaN(destinoId) || value === null || value === undefined) {
                        console.warn(`Omitiendo dato inválido para la clave: ${key}`);
                        continue;
                    }

                    matrizPayload.push({
                        id_matriz: idCounter++, // Asignar y luego incrementar
                        id_variable: origenId,
                        id_resp_depen: destinoId,
                        id_resp_influ: value
                    });
                }
            }

            if (matrizPayload.length === 0) {
                // Opcional: manejar el caso donde no hay nada que guardar
                return { success: true, message: 'No hay datos nuevos para guardar.' };
            }

            // Log para depuración final
            console.log("Enviando el siguiente payload al backend:", { matriz: matrizPayload });
            
            const response = await axios.post('/matriz', { matriz: matrizPayload });

            return { success: true, message: response.data.message || 'Matriz guardada correctamente.' };
        } catch (error) {
            console.error('Error al guardar la matriz:', error.response?.data || error.message);
            const message = error.response?.data?.message || 'Error al guardar la matriz.';
            return { success: false, message: message };
        } finally {
            isLoading.value = false;
        }
    }

    return {
        variables,
        isLoading,
        matrizData,
        getMatrizData,
        fetchMatrizData,
        updateMatrizValue,
        saveMatriz
    };
}); 