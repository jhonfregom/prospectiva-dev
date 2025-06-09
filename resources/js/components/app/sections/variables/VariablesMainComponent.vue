<script>
//Import general functions
import {
    capitalizeWords,
} from '../../../../functions';
//Import section from store
import { useSectionStore } from '../../../../stores/section';
//Import texts from store
import { useTextsStore } from '../../../../stores/texts';

export default {
    setup() {
        const storeSection = useSectionStore();
        const storeTexts = useTextsStore();
        return { storeSection, storeTexts };
    },
    components: {
    },
    mounted() {
        //Set title
        this.storeSection.setTitleSection(this.storeTexts.variables.title);
        this.storeSection.addDynamicButton('Nuevo', () => {
                newVariable.value = true; //Se debe crear un component para crear el registro.
            });
    },

    created() {
    },
    methods: {
        capitalizeWords, //This function is imported from functions.js
    }
}
</script>
<template>
    <div>
        <p>Content Variables</p>
    </div>
</template>