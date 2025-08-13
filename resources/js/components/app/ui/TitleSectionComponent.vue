<template>
    <section
        class="title-section">
        <div class="columns">
            <div class="column is-11-desktop is-12-touch is-12-tablet is-offset-1-desktop content-title">
                <template v-if="isMainSection && storeTexts.main_section">
                    <div class="columns header">
                        <div class="column">
                            <span class="title-group">{{ capitalizeWords(storeTexts.main_section?.modules_title || '') }}</span>
                        </div>
                        <div class="column">
                            <span class="title-group">{{ capitalizeWords(storeTexts.main_section?.queries_title || '') }}</span>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="title">{{ capitalizeWords(title) }}</div>
                    <div class="content-actions">
                        <a
                            v-for="(button, index) in dynamicButtons"
                            :key="index"
                            href="#"
                            v-on:click.prevent="button.action">
                            {{ capitalizeWords(button.label) }}
                        </a>
                        <template v-for="(component, index) in customComponents">
                            <!-- It's a custom component -->
                            <component v-if="typeof component.value === 'object'"
                                :is="component.value"
                                :key="'component-'+index"
                                v-on="component.actions"/>
                            <!-- It's a string HTML -->
                            <span v-else-if="typeof component === 'string'"
                                v-html="component"
                                :key="'html-'+index" />
                        </template>
                        <a
                            v-if="isVisibleBackBtn && storeTexts.global"
                            href="#"
                            v-on:click.prevent="toBack()">
                            {{ capitalizeWords(storeTexts.global?.go_back || '') }}
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>
<script>
    
    import {
        capitalizeWords,
    } from '../../../functions';
    
    import { useSectionStore } from '../../../stores/section';
    
    import { useTextsStore } from '../../../stores/texts';
    
    import { useSessionStore } from '../../../stores/session';

    export default {
        setup() {
            const storeSection = useSectionStore();
            const storeTexts = useTextsStore();
            const storeSession = useSessionStore();
            return { storeSection, storeTexts, storeSession };
        },
        data() {
            return {
            }
        },
        props: {
        },
        computed: {
            title() {
               return this.storeSection.getTitleSection;
            },
            dynamicButtons(){
                return this.storeSection.dynamicButtons;
            },
            customComponents(){
                return this.storeSection.customComponents;
            },
            isMainSection() {
                return this.storeSession.activeContent === 'main';
            },
            isVisibleBackBtn(){
                return this.storeSection.showBackButton;
            },
        },
        methods: {
            capitalizeWords,
            toBack() {
                this.storeSession.toBack();
            }
        }
    }
</script>

<style lang="scss" scoped>
    @use '../../../../sass/colors' as colors;

    .content-title{
        background-color: colors.$color-accent;
        border: 1px solid colors.$color-accent;
        border-radius: 16px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-left: 1.9rem;
        padding-right: 1.9rem;

        .header{
            width: 100%;
            .title-group{
                color: white;
            }
        }

        .title{
            color: white !important;
            display: inline-block;
            font-size: 1rem;
            margin-bottom: 0;
        }
        .content-actions{
            display: flex;
            gap: 1.9rem;
            a,
            :deep( a.link ){
                display: inline-block;
                color: white !important;
                font-weight: bolder;
            }
        }
    }
</style>