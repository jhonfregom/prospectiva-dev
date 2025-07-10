<template>
    <div class="variables-container">
        <b-message type="is-info" has-icon>
            {{ textsStore.getText('hypothesis.subtitle') }}
        </b-message>
        <b-table :data="drivers" :striped="true" :hoverable="true" :bordered="false" :narrowed="true" :loading="futureDriversStore.isLoading" icon-pack="fas">
            <b-table-column field="h" :label="textsStore.getText('hypothesis.table.h')" width="80" v-slot="props" centered>
                <span>H{{ props.index + 1 }}</span>
            </b-table-column>
            <b-table-column field="variable" :label="textsStore.getText('hypothesis.table.variable')" v-slot="props" centered>
                <span>{{ props.row.variable_name }}</span>
            </b-table-column>
            <b-table-column field="descriptionH0" :label="textsStore.getText('hypothesis.table.descriptionH0')" v-slot="props" centered header-class="hypothesis-header">
                <div class="edit-area">
                    <div class="textarea-container">
                        <b-input
                            v-model="localDescriptionsH0[props.row.variable_id]"
                            type="textarea"
                            :disabled="isLocked(props.row) || editingRow !== props.row.variable_id"
                            :placeholder="textsStore.getText('hypothesis.table.descriptionH0')"
                            :rows="3"
                            style="min-width:180px; max-width:350px; resize:vertical;"
                            @input="handleInput($event, props.row, 'H0')"
                            @keydown="handleKeyDown($event, props.row, 'H0')"
                            @paste="handlePaste($event, props.row, 'H0')"
                        />
                        <div class="word-counter" :class="getWordCountClass(countWords(localDescriptionsH0[props.row.variable_id]))">
                            {{ countWords(localDescriptionsH0[props.row.variable_id]) }}/40 palabras
                        </div>
                    </div>
                </div>
            </b-table-column>
            <b-table-column field="descriptionH1" :label="textsStore.getText('hypothesis.table.descriptionH1')" v-slot="props" centered header-class="hypothesis-header">
                <div class="edit-area">
                    <div class="textarea-container">
                        <b-input
                            v-model="localDescriptionsH1[props.row.variable_id]"
                            type="textarea"
                            :disabled="isLocked(props.row) || editingRow !== props.row.variable_id"
                            :placeholder="textsStore.getText('hypothesis.table.descriptionH1')"
                            :rows="3"
                            style="min-width:180px; max-width:350px; resize:vertical;"
                            @input="handleInput($event, props.row, 'H1')"
                            @keydown="handleKeyDown($event, props.row, 'H1')"
                            @paste="handlePaste($event, props.row, 'H1')"
                        />
                        <div class="word-counter" :class="getWordCountClass(countWords(localDescriptionsH1[props.row.variable_id]))">
                            {{ countWords(localDescriptionsH1[props.row.variable_id]) }}/40 palabras
                        </div>
                    </div>
                </div>
            </b-table-column>
            <b-table-column field="actions" :label="textsStore.getText('hypothesis.table.actions')" v-slot="props" centered>
                <div class="actions-column">
                    <b-button
                        type="is-info"
                        size="is-small"
                        icon-left="edit"
                        @click="handleEditSave(props.row, props.index)"
                        outlined
                        :disabled="isLocked(props.row)"
                    >
                        {{ editingRow === props.row.variable_id ? textsStore.getText('hypothesis.table.save') : textsStore.getText('hypothesis.table.edit') }}
                    </b-button>
                    <span v-if="isLocked(props.row)" class="tag is-warning ml-2">{{ textsStore.getText('hypothesis.table.locked') }}</span>
                </div>
            </b-table-column>
        </b-table>
        <div class="note-box mt-4">
            <b-message type="is-warning" has-icon>
                <strong>Nota:</strong> {{ textsStore.getText('hypothesis.note') }}
            </b-message>
        </div>
    </div>
</template>
<script>
import { useSectionStore } from '../../../../stores/section';
import { useFutureDriversStore } from '../../../../stores/futureDrivers';
import { useTextsStore } from '../../../../stores/texts';
import { ref, onMounted, watch, computed } from 'vue';
import { storeToRefs } from 'pinia';

export default {
    name: 'DirectionFutureMainComponent',
    setup() {
        console.log('=== COMPONENT SETUP START ===');
        
        const sectionStore = useSectionStore();
        const futureDriversStore = useFutureDriversStore();
        const textsStore = useTextsStore();
        const { drivers } = storeToRefs(futureDriversStore);
        const localDescriptionsH0 = ref([]);
        const localDescriptionsH1 = ref([]);
        const editingRow = ref(null);

        // Función para contar palabras
        const countWords = (text) => {
            if (!text) return 0;
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        };

        // Función para limitar palabras a 40
        const limitWords = (text, maxWords = 40) => {
            if (!text) return text;
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            if (words.length <= maxWords) return text;
            return words.slice(0, maxWords).join(' ');
        };

        // Computed para obtener el color del contador de palabras
        const getWordCountClass = (wordCount) => {
            if (wordCount > 40) return 'has-text-danger';
            if (wordCount > 35) return 'has-text-warning';
            return 'has-text-grey';
        };

        // Sincronizar los valores locales con los del store
        watch(
            () => futureDriversStore.drivers,
            (newVal) => {
                console.log('DirectionFuture - Watcher triggered, drivers:', newVal);
                if (newVal && newVal.length > 0) {
                    newVal.forEach(d => {
                        localDescriptionsH0.value[d.variable_id] = d.descriptionH0 || '';
                        localDescriptionsH1.value[d.variable_id] = d.descriptionH1 || '';
                    });
                    console.log('DirectionFuture - Local arrays updated from watcher');
                }
            },
            { immediate: true, deep: true }
        );

        // Watcher adicional para detectar cambios en los drivers y sincronizar los arrays locales usando variable_id como clave
        watch(drivers, (newDrivers) => {
            console.log('DirectionFuture - Drivers watcher triggered:', newDrivers);
            if (newDrivers && newDrivers.length > 0) {
                newDrivers.forEach((driver) => {
                    localDescriptionsH0.value[driver.variable_id] = driver.descriptionH0 || '';
                    localDescriptionsH1.value[driver.variable_id] = driver.descriptionH1 || '';
                });
                console.log('DirectionFuture - Local arrays updated from drivers watcher');
            }
        }, { immediate: true, deep: true });

        // Saber si está bloqueado - verificar tanto stateH0 como stateH1
        const isLocked = (row) => {
            // Si cualquiera de las dos hipótesis está bloqueada, toda la fila se bloquea
            const locked = (row.stateH0 === '1' || row.stateH1 === '1');
            console.log('isLocked check:', { 
                variable_id: row.variable_id, 
                stateH0: row.stateH0, 
                stateH1: row.stateH1, 
                locked: locked 
            });
            return locked;
        };

        const handleEditSave = async (row, index) => {
            console.log('handleEditSave called:', { 
                variable_id: row.variable_id, 
                index: index, 
                stateH0: row.stateH0, 
                stateH1: row.stateH1,
                isLocked: isLocked(row)
            });
            
            if (isLocked(row)) {
                console.log('Row is locked, cannot edit');
                return;
            }

            if (editingRow.value === row.variable_id) {
                // Guardar ambas hipótesis (H0 y H1) para esta variable
                const h0Text = localDescriptionsH0.value[row.variable_id] || '';
                const h1Text = localDescriptionsH1.value[row.variable_id] || '';
                
                // Usar saveBothHypotheses que guarda H0 y H1 automáticamente
                const result = await futureDriversStore.saveBothHypotheses(
                    row.variable_id,  // variableId
                    'H' + (index + 1), // nameHypothesis (H1 o H2)
                    h0Text,           // h0Text
                    h1Text,           // h1Text
                    row.zone_id || 1, // zoneId
                    '0'  // state - siempre enviar '0' para que el backend maneje el conteo
                );
                
                if (result && result.success) {
                    editingRow.value = null;
                    // Recargar los datos para obtener el estado actualizado
                    await futureDriversStore.fetchDrivers();
                }
            } else {
                editingRow.value = row.variable_id;
            }
        };

        // Manejar input con límite de palabras usando variable_id
        const handleInput = (event, row, type) => {
            const text = event.target.value;
            const wordCount = countWords(text);
            
            // Si ya hay más de 40 palabras, recortar
            if (wordCount > 40) {
                const limitedText = limitWords(text, 40);
                if (type === 'H0') {
                    localDescriptionsH0.value[row.variable_id] = limitedText;
                } else {
                    localDescriptionsH1.value[row.variable_id] = limitedText;
                }
                return;
            }
            
            // Si está dentro del límite, permitir escribir
            if (type === 'H0') {
                localDescriptionsH0.value[row.variable_id] = text;
            } else {
                localDescriptionsH1.value[row.variable_id] = text;
            }
        };

        // Función para manejar keydown - BLOQUEAR solo cuando se va a crear la palabra 41
        const handleKeyDown = (event, row, type) => {
            const text = event.target.value;
            const wordCount = countWords(text);
            
            // Solo bloquear si ya hay más de 40 palabras
            if (wordCount > 40 && 
                !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter'].includes(event.key)) {
                event.preventDefault();
            }
        };

        // Función específica para manejar pegado
        const handlePaste = (event, row, type) => {
            event.preventDefault();
            const pastedText = event.clipboardData.getData('text');
            const currentText = event.target.value;
            const combinedText = currentText + pastedText;
            const limitedText = limitWords(combinedText, 40);
            
            if (type === 'H0') {
                localDescriptionsH0.value[row.variable_id] = limitedText;
            } else {
                localDescriptionsH1.value[row.variable_id] = limitedText;
            }
        };

        onMounted(async () => {
            console.log('=== COMPONENT MOUNTED ===');
            console.log('DirectionFuture - onMounted - Component mounted');
            console.log('DirectionFuture - onMounted - Store available:', !!futureDriversStore);
            
            sectionStore.setTitleSection('Direccionadores de futuro');
            
            // Cargar datos siempre al montar el componente
            console.log('DirectionFuture - onMounted - Starting data load...');
            await futureDriversStore.fetchDrivers();
            console.log('DirectionFuture - onMounted - Data load finished');
            
            console.log('=== COMPONENT MOUNTED END ===');
        });

        return {
            sectionStore,
            futureDriversStore,
            textsStore,
            drivers,
            localDescriptionsH0,
            localDescriptionsH1,
            editingRow,
            isLocked,
            handleEditSave,
            countWords,
            getWordCountClass,
            handleInput,
            handleKeyDown,
            handlePaste
        };
    },
}
</script>



<style scoped>
.variables-container {
    padding: 20px;
}
.b-table {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    font-size: 14px;
}
/* Compactar solo la fila de encabezados (thead th) */
::v-deep .b-table .table thead th {
    height: 40px !important;
    min-height: 0 !important;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
}
/* Centrado vertical SOLO en celdas de la tabla Buefy */
::v-deep .b-table .table th,
::v-deep .b-table .table td {
    vertical-align: middle !important;
    height: 100px !important;
}
.b-table th {
    background-color: #EEF2FF;
    color: #4F46E5;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
    border: none;
    border-bottom: 2px solid #E0E7FF;
    min-height: 60px;
}
.b-table td {
    background-color: #fff;
    color: #1F2937;
    font-weight: 500;
    border: none;
    border-bottom: 1px solid #E0E7FF;
}
.b-table tr:nth-child(even) td {
    background-color: #FAFAFA;
}
.b-table tr:nth-child(odd) td {
    background-color: #FFFFFF;
}
/* Centrar contenido de celdas de texto y acciones */
.edit-area,
.textarea-container,
.actions-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.textarea-container {
    position: relative;
}
.word-counter {
    font-size: 11px;
    margin-top: 4px;
    text-align: right;
    font-weight: 500;
    width: 100%;
}
.b-table-column span {
    display: inline-block;
    vertical-align: middle;
}
.note-box {
    max-width: 600px;
    margin: 0 auto;
}
th.hypothesis-header {
    min-width: 180px;
    max-width: 350px;
    background: #f5f6fa;
    color: #b0b3c6;
    font-weight: 500;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 12px;
}
.b-table td:first-child, .b-table td:nth-child(2) {
    height: 60px;
}
.b-table .textarea-container .input {
    margin: 0;
}
.b-table td > * {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>