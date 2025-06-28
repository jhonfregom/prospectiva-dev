<script>
import { useSectionStore } from '../../../../stores/section';
    export default {
        components: {
        },

        setup() {
            
        const sectionStore = useSectionStore();

        return { 
            sectionStore,
        };
        },

        mounted() {
        this.sectionStore.setTitleSection('Variables');
        this.sectionStore.addDynamicButton('Nuevo', () => {
            this.showModal = true;
        });
    },
    
    };
</script>
<template>
    <div >
        <main-section>
            <div class="content-main">
                <!-- Aquí se puede agregar el contenido principal del análisis de mapa de variables -->
                <h1>Contenido del Análisis de Mapa de Variables</h1>
                <!-- Puedes agregar más componentes o lógica aquí según sea necesario -->
            </div>
        </main-section>

    </div>
</template>