<script>

import { useSectionStore } from '../../../../stores/section';


export default {


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

}

</script>
<template>
    <div class="main-content">
       
    </div>
</template>