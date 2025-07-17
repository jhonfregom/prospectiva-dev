<template>
    <nav id="navbar-main"
        class="navbar"
        >
        <div class="navbar-brand">
            <div class="navbar-item">
                <!-- Bell notification -->
                <navbar-notification />

                <!-- Content entity name and selector account -->
                <navbar-company
                    v-bind:dataParticipantsUser="dataParticipantsUser"
                    v-on:actionReloadGeneralData="reloadGeneralData" />
            </div>
        </div>
        <div class="navbar-menu">
            <div class="navbar-end">
                <b-button
                    class="is-primary"
                    v-on:click="logout"
                >
                    Logout
                </b-button>
            </div>
            <span class="icon"><i class="fas fa-bars"></i></span>
        </div>
    </nav>
</template>
<script>
    //Import url from store
    import { useUrlsStore } from '../../../../stores/urls';
    //Import NavbarRightItemComponent
    import navbarCompany from './NavbarCompanyComponent.vue';
    //Import NavbarNotificationComponent
    import navbarNotification from './NavbarNotificationComponent.vue';

    export default {
        setup() {
            const storeUrls = useUrlsStore();
            return { storeUrls };
        },
        components: {
            navbarCompany,
            navbarNotification,
        },
        props: {
            dataParticipantsUser: {
                type: Object,
                required: true,
            },
        },
        methods: {
            reloadGeneralData(accountData){
                this.$emit("actionReloadGeneralData", accountData);
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
        }
    }
</script>


<style lang="scss" scoped>
    .notify-content{
        margin-top: 0.5rem;
        margin-right: 0.5rem;
    }
</style>
