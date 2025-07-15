<template>
    <div class="matriz-container">
        <!-- MiniStepper eliminado -->
        <div class="matriz-header">
            <b-button
                type="is-primary"
                icon-left="save"
                @click="guardarMatriz"
                :loading="isLoading"
                :disabled="isLocked || isLoading">
                {{ textsStore.getText('matriz.save') }}
            </b-button>
        </div>

        <div class="matriz-table-container">
            <b-loading :is-full-page="false" v-model="isLoading" :can-cancel="false"></b-loading>
            
            <div v-if="!isLoading && orderedVariables.length === 0" class="empty-state-container">
                <p>{{ textsStore.getText('matriz.no_variables_message') }}</p>
                <p>
                    {{ textsStore.getText('matriz.create_variables_message_part1') }}
                    <a @click="$router.push({ name: 'variables' })">{{ textsStore.getText('matriz.create_variables_link_text') }}</a>
                    {{ textsStore.getText('matriz.create_variables_message_part2') }}
                </p>
            </div>

            <table class="matriz-table" v-if="orderedVariables.length > 0">
                <thead>
                    <tr>
                        <th class="matriz-header-cell matriz-header-bg matriz-codigo-cell">{{ textsStore.getText('matriz.code') }}</th>
                        <th class="matriz-header-cell matriz-header-bg matriz-nombre-cell">{{ textsStore.getText('matriz.name') }}</th>
                        <th v-for="variable in orderedVariables" 
                            :key="'header-' + variable.id"
                            class="matriz-header-cell matriz-header-bg matriz-data-cell matriz-col-align">
                            {{ variable.id_variable }}
                        </th>
                        <th class="matriz-header-cell matriz-header-bg matriz-total-cell">{{ textsStore.getText('matriz.total_dependency') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(varOrigen, rowIdx) in orderedVariables" :key="'row-' + varOrigen.id" :class="{'matriz-row-alt': rowIdx % 2 === 1}">
                        <td class="matriz-cell-header matriz-header-bg matriz-codigo-cell matriz-col-align">{{ varOrigen.id_variable }}</td>
                        <td class="matriz-cell-header matriz-header-bg matriz-nombre-cell">{{ varOrigen.name_variable }}</td>
                        <td v-for="(varDestino, colIdx) in orderedVariables"
                            :key="'cell-' + varOrigen.id + '-' + varDestino.id"
                            :class="['matriz-cell-center', 'matriz-col-align', 'matriz-data-cell', getCellClass(varOrigen.id, varDestino.id), {'locked': isLocked}]"
                            @click="handleCellClick(varOrigen.id, varDestino.id, $event)">
                            <div style="position: relative; width: 100%; height: 100%;">
                                <div v-if="!isLocked && origenIdPopover === varOrigen.id && destinoIdPopover === varDestino.id" class="matriz-float-menu-abs">
                                    <button v-for="val in [0,1,2,3]" :key="val" @click.stop="selectPopoverValue(varOrigen.id, varDestino.id, val)" :class="'matriz-float-btn matriz-value-' + val + '-modern'">{{ val }}</button>
                                </div>
                                <div v-else :class="getColorClase(getCellValue(varOrigen.id, varDestino.id)) + '-modern'">
                                    {{ getCellValue(varOrigen.id, varDestino.id) }}
                                </div>
                            </div>
                        </td>
                        <td class="matriz-total-cell matriz-total-bg matriz-col-align">{{ totalesInfluencia[varOrigen.id] || 0 }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="matriz-total-header-cell matriz-total-bg matriz-col-align matriz-codigo-cell" colspan="2">{{ textsStore.getText('matriz.total_influence') }}</td>
                        <td v-for="variable in orderedVariables" :key="'footer-' + variable.id" class="matriz-total-cell matriz-total-bg matriz-col-align matriz-data-cell">
                            {{ totalesDependencia[variable.id] || 0 }}
                        </td>
                        <td class="matriz-total-cell matriz-total-bg matriz-col-align">{{ sumaTotalDependencia }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Leyenda -->
        <div class="matriz-legend">
            <h3>{{ textsStore.getText('matriz.interpretation_title') }}</h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="legend-value legend-strong">3</span>
                    <span>{{ textsStore.getText('matriz.strong_influence') }}</span>
                </div>
                <div class="legend-item">
                    <span class="legend-value legend-medium">2</span>
                    <span>{{ textsStore.getText('matriz.medium_influence') }}</span>
                </div>
                <div class="legend-item">
                    <span class="legend-value legend-weak">1</span>
                    <span>{{ textsStore.getText('matriz.weak_influence') }}</span>
                </div>
                <div class="legend-item">
                    <span class="legend-value legend-none">0</span>
                    <span>{{ textsStore.getText('matriz.null_influence') }}</span>
                </div>
            </div>
        </div>

        <!-- Tabla de Resumen -->
        <div class="matriz-resumen">
            <table class="resumen-table">
                <thead>
                    <tr>
                        <th>{{ textsStore.getText('matriz.summary') }}</th>
                        <th v-for="variable in orderedVariables" 
                            :key="'resumen-' + variable.id">
                            {{ variable.id_variable }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ textsStore.getText('matriz.dependency') }}</td>
                        <td v-for="variable in orderedVariables" 
                            :key="'dep-' + variable.id">
                            {{ totalesInfluencia[variable.id] || 0 }}
                        </td>
                    </tr>
                    <tr>
                        <td>{{ textsStore.getText('matriz.influence') }}</td>
                        <td v-for="variable in orderedVariables" 
                            :key="'inf-' + variable.id">
                            {{ totalesDependencia[variable.id] || 0 }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import { useMatrizStore } from '../../../../stores/matriz';
import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { storeToRefs } from 'pinia';
import MiniStepper from '../../ui/MiniStepper.vue';

export default {
    components: {
        MiniStepper
    },
    setup() {
        const matrizStore = useMatrizStore();
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const { variables, isLoading, isLocked } = storeToRefs(matrizStore);

        return {
            matrizStore,
            sectionStore,
            textsStore,
            variables,
            isLoading,
            isLocked
        };
    },

    data() {
        return {
            editingCell: null,
            origenIdPopover: null,
            destinoIdPopover: null,
            popoverStyle: {},
            steps: [
                { key: 'variables', label: 'Variables', icon: 'fas fa-list' },
                { key: 'matrix', label: 'Matriz', icon: 'fas fa-th' },
                { key: 'graphics', label: 'Gráfica', icon: 'fas fa-chart-bar' },
                { key: 'analysis', label: 'Mapa', icon: 'fas fa-map' },
                { key: 'hypothesis', label: 'Direccionador', icon: 'fas fa-bolt' },
                { key: 'schwartz', label: 'Schwartz', icon: 'fas fa-project-diagram' },
                { key: 'initialconditions', label: 'Condiciones', icon: 'fas fa-flag' },
                { key: 'scenarios', label: 'Escenarios', icon: 'fas fa-cubes' },
                { key: 'conclusions', label: 'Conclusiones', icon: 'fas fa-lightbulb' },
                { key: 'results', label: 'Resultados', icon: 'fas fa-trophy' },
                { key: 'nueva', label: 'Nueva', icon: 'fas fa-star' },
            ]
        };
    },

    computed: {
        matrizData() {
            return this.matrizStore.getMatrizData;
        },
        totalesInfluencia() {
            const totales = {};
            this.variables.forEach(variable => {
                totales[variable.id] = 0;
                this.variables.forEach(varDestino => {
                    if (variable.id !== varDestino.id) {
                        totales[variable.id] += this.getCellValue(variable.id, varDestino.id);
                    }
                });
            });
            return totales;
        },
        totalesDependencia() {
            const totales = {};
            this.variables.forEach(variable => {
                totales[variable.id] = 0;
                this.variables.forEach(varOrigen => {
                    if (variable.id !== varOrigen.id) {
                        totales[variable.id] += this.getCellValue(varOrigen.id, variable.id);
                    }
                });
            });
            return totales;
        },
        sumaTotalDependencia() {
            return Object.values(this.totalesDependencia).reduce((sum, valor) => sum + valor, 0);
        },
        orderedVariables() {
            return [...this.variables].sort((a, b) => {
                const numA = parseInt(a.id_variable.replace('V', ''));
                const numB = parseInt(b.id_variable.replace('V', ''));
                return numA - numB;
            });
        }
    },

    mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('matriz.section_title'));
        this.loadMatrizData();
        document.addEventListener('click', this.closePopover);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.closePopover);
    },

    methods: {
        async loadMatrizData() {
            await this.matrizStore.fetchMatrizData();
        },

        getCellValue(origenId, destinoId) {
            if (origenId === destinoId) return 'X';
            const key = `${origenId}-${destinoId}`;
            return this.matrizStore.matrizData[key] !== undefined ? this.matrizStore.matrizData[key] : 0;
        },

        getCellClass(origenId, destinoId) {
            if (origenId === destinoId) {
                return 'matriz-cell-diagonal';
            }
            return 'matriz-cell-editable';
        },

        handleCellClick(origenId, destinoId, event) {
            if (this.isLocked || this.isLoading) return;
            event.stopPropagation();
            if (origenId === destinoId) return;
            this.origenIdPopover = origenId;
            this.destinoIdPopover = destinoId;
            // Calcular posición absoluta del menú flotante
            const rect = event.target.getBoundingClientRect();
            this.popoverStyle = {
                position: 'fixed',
                top: rect.top + window.scrollY + 'px',
                left: rect.left + window.scrollX + 'px',
                zIndex: 9999
            };
        },

        closePopover() {
            this.origenIdPopover = null;
            this.destinoIdPopover = null;
        },

        selectPopoverValue(origenId, destinoId, valor) {
            this.matrizStore.updateMatrizValue(origenId, destinoId, valor);
            this.closePopover();
        },

        async guardarMatriz() {
            const result = await this.matrizStore.saveMatriz(this.textsStore);
            this.$buefy.toast.open({
                message: result.message,
                type: result.success ? 'is-success' : 'is-danger',
                position: 'is-top'
            });
        },

        getColorClase(valor) {
            if (typeof valor === 'string') return '';
            switch(valor) {
                case 3: return 'matriz-value-strong';
                case 2: return 'matriz-value-medium';
                case 1: return 'matriz-value-weak';
                default: return 'matriz-value-none';
            }
        },

        handleClickOutside(event) {
            // Si el clic fue en un input, no hacer nada
            if (event.target.tagName.toLowerCase() === 'input') {
                return;
            }

            const matrizTable = this.$el.querySelector('.matriz-table');
            if (matrizTable && !matrizTable.contains(event.target)) {
                this.$nextTick(() => {
                    this.editingCell = null;
                });
            }
        }
    }
};
</script>

<style lang="scss" scoped>
.matriz-container {
    padding: 2rem;
    background-color: #f8f9fa;
    min-height: 100vh;

    .matriz-header {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 2rem;
    }

    .matriz-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .matriz-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;

        .matriz-header-cell {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
            border: none;
            border-bottom: 2px solid #E0E7FF;
            min-width: 60px;
        }

        .matriz-row-header {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
            border: none;
            border-right: 2px solid #E0E7FF;
            min-width: 80px;
        }

        .matriz-cell {
            border: none;
            padding: 1rem;
            text-align: center;
            min-width: 60px;
            height: 50px;
            vertical-align: middle;
            position: relative;
            background-color: white;

            &.matriz-cell-diagonal {
                background-color: #F5F3FF;
                cursor: not-allowed;
                font-weight: 600;
                color: #6D28D9;
                border-right: 1px solid #EDE9FE;
                border-bottom: 1px solid #EDE9FE;
            }

            &.matriz-cell-editable {
                cursor: pointer;
                border-right: 1px solid #EDE9FE;
                border-bottom: 1px solid #EDE9FE;
                background-color: #FAFAFA;

                &:hover:not(.editing) {
                    background-color: #F5F3FF;
                }

                &.editing {
                    background-color: #F5F3FF;
                    box-shadow: inset 0 0 0 2px #818CF8;
                    z-index: 2;
                }
            }

            &.locked {
                cursor: not-allowed;
                background-color: #f5f5f5;
            }
        }

        .matriz-total-cell {
            background-color: #F0FDF4;
            color: #166534;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            border: none;
            border-left: 2px solid #DCFCE7;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .matriz-total-header-cell {
            background-color: #F0FDF4;
            color: #166534;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border: none;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) .matriz-cell-editable {
            background-color: #FAFAFA;
        }

        tr:nth-child(odd) .matriz-cell-editable {
            background-color: #FFFFFF;
        }
    }

    .matriz-input {
        width: 100%;
        height: 100%;
        text-align: center;
        border: none;
        padding: 0;
        font-size: 14px;
        font-weight: 500;
        color: #2c3e50;
        background-color: transparent;

        &:focus {
            outline: none;
        }
    }

    .matriz-value-strong {
        color: #DC2626;
        font-weight: 600;
        font-size: 16px;
    }

    .matriz-value-medium {
        color: #EA580C;
        font-weight: 600;
        font-size: 16px;
    }

    .matriz-value-weak {
        color: #CA8A04;
        font-weight: 600;
        font-size: 16px;
    }

    .matriz-value-none {
        color: #6B7280;
        font-size: 14px;
        font-weight: 500;
    }

    .matriz-legend {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);

        h3 {
            margin-bottom: 1rem;
            color: #4F46E5;
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: #FAFAFA;

            &:hover {
                background-color: #F5F3FF;
            }

            .legend-value {
                width: 35px;
                height: 35px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2s ease;

                &.legend-strong {
                    background-color: #FEE2E2;
                    color: #DC2626;
                }

                &.legend-medium {
                    background-color: #FFEDD5;
                    color: #EA580C;
                }

                &.legend-weak {
                    background-color: #FEF9C3;
                    color: #CA8A04;
                }

                &.legend-none {
                    background-color: #F3F4F6;
                    color: #6B7280;
                }
            }

            span:not(.legend-value) {
                color: #1F2937;
                font-weight: 500;
            }
        }
    }

    .matriz-resumen {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        margin-top: 2rem;

        .resumen-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;

            th, td {
                border: none;
                padding: 12px 8px;
                text-align: center;
            }

            th {
                background-color: #EEF2FF;
                color: #4F46E5;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 13px;
                border-bottom: 2px solid #E0E7FF;
            }

            td:first-child {
                background-color: #EEF2FF;
                color: #4F46E5;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 13px;
                border-right: 2px solid #E0E7FF;
            }

            td {
                background-color: #FAFAFA;
                color: #1F2937;
                font-weight: 500;

                &:nth-child(even) {
                    background-color: #F8FAFC;
                }
            }

            tr:last-child td {
                border-top: 1px solid #E0E7FF;
            }
        }
    }
}

// Ocultar flechas del input number
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style> 