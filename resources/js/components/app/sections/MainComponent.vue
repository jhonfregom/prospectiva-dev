<script>
    //Import texts from store
    import { useTextsStore } from '../../../stores/texts';
    //Import session from store
    import { useSessionStore } from '../../../stores/session';

    export default {
        setup(){
            const storeTexts = useTextsStore();
            const storeSession = useSessionStore();
            return { storeTexts, storeSession };
        },
        methods: {
            setActiveSection(item){
                this.storeSession.setActiveContent( item );
            },
        }
    }
</script>
<template>
    <div class="main-content">
    <section class="intro-section">
    <h2>Introducción</h2>
    <p>
        La estrategia empresarial como clave para el desarrollo regional, se impone como una tarea que debe ser tenida en cuenta desde el análisis de la actualidad empresarial y contrastada con el análisis de la actualidad del entorno empresarial. Esta de manera definitiva deber dar cuenta de los requerimientos que deben implementar las organizaciones frente a sus futuros más deseables, en un entorno cada vez más incierto.
    </p>
    <p>
        La generación de una visión a futuro generado con estrategias altamente efectivas aportará a las empresas y al sector al que pertenezca una gran ventaja competitiva, generando valor no solo para la organización sino para quienes estas logren impactar.
    </p>
    <p>
        Por lo tanto y con el presente ejercicio prospectivo se pretende hacer una revisión detallada de cada variable a intervenir y como mediante una evaluación consciente de las mismas es posible aportar estratégicamente a esa visión de futuro que las empresas cada vez requieren con mayor urgencia.
    </p>
    <p>
        A continuación, se relaciona la tendencia, el tópico o tema a ser analizado a lo largo de esta herramienta, ten en cuenta todas las indicaciones del manual para su fácil comprensión y ejecución.
    </p>
</section>
        <section class="dashboard columns">
            <section class="modules column columns is-multiline">
                <div v-for="( module, index ) in storeTexts.main_section.modules"
                    :key="index"
                    class="column is-6">
                    <div class="card"
                        v-on:click.prevent="setActiveSection(index)">
                        <header class="card-header">
                            <p class="card-header-title">
                                {{ module.title }}
                            </p>
                        </header>
                        <div class="card-content">
                            <p v-for="(descp, index) in module.description"
                                :key="index">
                                {{ descp }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <!--<section class="separator column is-1" />
            <section class="queries column columns is-multiline">
                <div v-for="( query, index ) in storeTexts.main_section.queries"
                    :key="index"
                    class="column is-6">
                    <div class="card"
                        v-on:click.prevent="setActiveSection(index)">
                        <header class="card-header">
                            <p class="card-header-title">
                                {{ query.title }}
                            </p>
                        </header>
                        <div class="card-content">
                            <p v-for="(descp, index) in query.description"
                                :key="index">
                                {{ descp }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>-->
        </section>
    </div>
</template>
<style lang="scss" scoped>
.modules, .queries {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    padding: 20px 0;
}

.modules .column, .queries .column {
    flex: 0 1 auto;
    width: auto;
}

.modules .card, .queries .card {
    min-width: 200px;
    max-width: 250px;
    padding: 20px;
    border-radius: 12px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    text-align: center;
}

.modules .card:hover, .queries .card:hover {
    background-color: #6a1b9a; /* color morado */
    color: white;
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.modules .card-header-title, .queries .card-header-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: inherit;
}

.modules .card-content p, .queries .card-content p {
    font-size: 0.95rem;
    color: inherit;
}

.intro-section {
    background-color: #5090c0; /* el rojo que tienes en la imagen */
    color: white;
    border-radius: 8px;
    padding: 20px 30px;
    margin: 20px 0; /* separación con el navbar y con el menú */
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


</style>
