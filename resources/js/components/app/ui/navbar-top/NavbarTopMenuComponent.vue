<template>
    <nav id="navbar-main" class="navbar">
        <div class="navbar-brand">
            <div class="navbar-item">
                <!-- Bell notification -->
                <navbar-notification />

                <!-- Content entity name and selector account -->
                <navbar-company
                    v-bind:dataParticipantsUser="dataParticipantsUser"
                    v-on:actionReloadGeneralData="reloadGeneralData" />
            </div>
            
            <!-- Mobile menu burger -->
            <a role="button" 
               class="navbar-burger" 
               :class="{ 'is-active': isMenuOpen }"
               @click="toggleMenu"
               aria-label="menu" 
               aria-expanded="false">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        
        <div class="navbar-menu" :class="{ 'is-active': isMenuOpen }">
            <div class="navbar-end">
                <b-button
                    class="is-primary"
                    v-on:click="logout"
                >
                    Cerrar Sesión
                </b-button>
            </div>
        </div>
    </nav>
</template>
<script>
    
    import { useUrlsStore } from '../../../../stores/urls';
    
    import navbarCompany from './NavbarCompanyComponent.vue';
    
    import navbarNotification from './NavbarNotificationComponent.vue';

    export default {
        setup() {
            const storeUrls = useUrlsStore();
            return { storeUrls };
        },
        data() {
            return {
                isMenuOpen: false
            };
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
            toggleMenu() {
                this.isMenuOpen = !this.isMenuOpen;
            },
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
    
    /* Responsive navbar styles */
    .navbar {
        @media (max-width: 768px) {
            .navbar-brand {
                width: 100%;
                justify-content: space-between;
                
                .navbar-item {
                    flex: 1;
                    justify-content: flex-start;
                }
            }
            
            .navbar-menu {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 1000;
                display: none;
                
                &.is-active {
                    display: block;
                }
                
                .navbar-end {
                    padding: 1rem;
                    text-align: center;
                    
                    .button {
                        width: 100%;
                        margin: 0.5rem 0;
                    }
                }
            }
        }
        
        @media (max-width: 480px) {
            .navbar-brand {
                .navbar-item {
                    font-size: 0.9rem;
                }
            }
            
            .navbar-menu {
                .navbar-end {
                    padding: 0.5rem;
                    
                    .button {
                        font-size: 0.9rem;
                        padding: 0.75rem 1rem;
                    }
                }
            }
        }
    }
</style>