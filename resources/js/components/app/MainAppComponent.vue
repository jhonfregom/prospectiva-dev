<template>
    <div class="main">
        <b-loading class="main-app" :is-full-page="true" v-model="isLoading" :can-cancel="false" />
        <!--Navbar top -->
        <navbar-top
            v-if="isLoadedResources"
            v-bind:dataParticipantsUser="dataParticipantsUser"
            v-on:actionReloadGeneralData="reloadGeneralData"/>
        <!-- /Navbar top -->

        <!-- Content Main -->
        <content-main
            v-if="isLoadedResources"
            />
        <!-- /Content Main -->
        
        <!-- Componente de burbuja flotante - siempre disponible -->
        <floating-bubble-component 
          v-if="isLoadedResources"
        />
    </div>
</template>

<script>
    //Import general functions
    import {
        procesarErroresRequest,
    } from '../../functions.js';
    //Import url from store
    import { useUrlsStore } from '../../stores/urls';
    //Import texts from store
    import { useTextsStore } from '../../stores/texts';
    //Import fields from store
    import { useFieldsStore } from '../../stores/fields';
    //Import session from store
    import { useSessionStore } from '../../stores/session';
    //Import traceability from store
    import { useTraceabilityStore } from '../../stores/traceability';
    //Import NavbarTop
    import navbarTop from './ui/navbar-top/NavbarTopMenuComponent.vue';
    //Import ContentMain
    import contentMain from './layouts/ContentMainComponent.vue';
    //Import FloatingBubbleComponent
import FloatingBubbleComponent from './ui/FloatingBubbleComponent.vue';
    //Import Vue composables
    import { computed } from 'vue';

    export default {
        setup() {
            const storeUrls = useUrlsStore();
            const storeTexts = useTextsStore();
            const storeFields = useFieldsStore();
            const storeSession = useSessionStore();

            // Computed para obtener el traceabilityId actual
            const currentTraceabilityId = computed(() => {
                // Por ahora retornar null, el componente manejará la lógica internamente
                return null;
            });

            return { 
                storeUrls, 
                storeTexts, 
                storeFields, 
                storeSession,
                currentTraceabilityId
            };
        },
        components: {
            navbarTop,
            contentMain,
            FloatingBubbleComponent,
        },
        props: {
            urls_json: {
                type: String,
                required: true,
            },
            texts_json: {
                type: String,
                required: true,
            },
            fields_json: {
                type: String,
                required: true,
            },
            locale: {
                type: String,
                required: true,
            },
        },
        data() {
            return {
                isLoading: false,
                errors: null,
                hasErrors: false,
                dataParticipantsUser: new Object(),
                company: new Object(),
                participant: new Object(),
            }
        },
        computed: {
            isLoadedResources(){
                return true;
                //return !_.isEmpty( this.company ) && !_.isEmpty( this.participant );
            },
        },
        created() {
            this.initUrlsStore();
            this.initTextsStore();
            this.initFieldsStore();
        },
        async mounted() {
        //   await this.getInitData();
        },
        methods: {
            showErrors(resError){
                this.errors = procesarErroresRequest( resError );
                this.hasErrors = this.errors.errors.length > 0;
                //Alert Errors
                if( this.hasErrors )
                {
                    let msgErrors = "<ul>";
                    this.errors.errors.forEach( error => {
                        msgErrors = msgErrors.concat('<li>', error);
                        msgErrors = msgErrors.concat('', '</li>');
                    });
                    msgErrors = msgErrors.concat('', '</ul>');
                    error({
                                title: this.errors.text,
                                textTrusted: true,
                                text: msgErrors
                            });
                }
            },
            initUrlsStore(){
                let urls = JSON.parse( this.urls_json );
                this.storeUrls.setUrls( urls );
            },
            initTextsStore(){
                let texts = JSON.parse( this.texts_json );
                this.storeTexts.setTexts( texts );
                this.storeTexts.setLocale( this.locale );
            },
            initFieldsStore(){
                let fields = JSON.parse( this.fields_json );
                this.storeFields.setFields( fields );
            },
            /*async getInitData(){
                this.isLoading = true;

                await axios.post( this.storeUrls.participant.list_from_auth )
                    .then( response => {
                        this.showErrors({});
                        if( response.data.status === 200 )
                        {
                            //Set list participants to user
                            this.dataParticipantsUser = response.data.data;
                            if( Object.keys(this.dataParticipantsUser).length > 0 )
                            {
                                //Get first element, you will always have at least one
                                const firstIndex = Object.keys(this.dataParticipantsUser)[0];
                                const firstElement = this.dataParticipantsUser[ firstIndex ];
                                //Set participant and global from the first
                                this.storeSession.participant = firstElement['participant'];
                                this.participant = this.storeSession.participant;
                                //Set Company
                                this.storeSession.company= firstElement['company'];
                                this.company = this.storeSession.company;
                            }

                            //Validate if has access from membership
                            // this.validateAccessFromMembership();
                        }
                    },
                    error => {
                        this.showErrors(error);
                    })
                    .then( () => {
                        this.isLoading = false;
                    });
            },*/
            reloadGeneralData(accountData){
                //TODO change company and participant
                //clean data for reload section active
            },
            logout(){
                axios.post( this.storeUrls.logout )
                    .then( res => {
                        if( res.data.status === 200 ){
                            this.redirectToLogin();
                        }
                    });
            },
            redirectToLogin(){
                window.location.href = this.storeUrls.home;
            },
        },
    }
</script>
