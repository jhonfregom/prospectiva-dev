<template>
    <div class="main-content">
        <section class="intro-section">
            <h2>{{ storeTexts.variables.title_introduction }}</h2>
            <p>
                {{ storeTexts.variables.content_introduction }}
            </p>
        </section>
        <!-- Stepper principal grande debajo de la introducción -->
        <StepperPrincipal class="stepper-main-large" />
    </div>
</template>

<script>
    import { useTextsStore } from '../../../stores/texts';
    import { useSessionStore } from '../../../stores/session';
    import StepperPrincipal from '../ui/StepperPrincipal.vue';

    export default {
        components: {
            StepperPrincipal
        },
        setup(){
            const storeTexts = useTextsStore();
            const storeSession = useSessionStore();
            return { storeTexts, storeSession };
        }
    }
</script>

<style lang="scss" scoped>
.intro-section {
    background-color: #5090c0;
    color: white;
    border-radius: 8px;
    padding: 20px 30px;
    margin: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    line-height: 1.6;
}

.intro-section h2 {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 15px;
}

.intro-section p {
    margin-bottom: 10px;
    font-size: 1rem;
}

.stepper-main-large {
    margin-top: 40px;
    margin-bottom: 40px;
    /* Tamaño mucho más grande para el stepper */
    ::v-deep .stepper-circle {
        width: 120px !important;
        height: 120px !important;
        font-size: 4.2rem !important;
    }
    ::v-deep .stepper-label {
        font-size: 2.2rem !important;
        min-width: 160px;
        max-width: 260px;
    }
    ::v-deep .stepper-track {
        max-width: 98vw !important;
        min-width: 0;
        width: 98vw !important;
        justify-content: space-between !important;
    }
    ::v-deep .stepper-step {
        flex: 1 1 0 !important;
    }
    ::v-deep .stepper-line {
        height: 6px !important;
        top: 60px !important;
    }
}
</style>
