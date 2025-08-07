<template>
    <section class="company-content">
        <span class="name-entity">{{ capitalizeWords( company.entity_name ) }}</span>
        <b-dropdown v-if="isEnabledSelectorCompany"
            class="dropdown-account"
            position="is-bottom-left"
            trap-focus
            aria-role="list">
            <template #trigger="{ active }" >
                <b-button
                    class="button btn-account-selector">
                    <b-icon :icon="active ? 'fas fa-caret-up' : 'fas fa-caret-down'"/>
                </b-button>
            </template>
            <b-dropdown-item
                v-for="(account,index) in dataParticipantsUser"
                :key="'account-item-'+ index"
                aria-role="listitem"
                :class="{ 'is-current' : company.id === account.company.id }"
                :disabled="company.id === account.back.id "
                v-on:click="clickChangeAccount(account)"
            >
                {{ capitalizeWords( account.company.entity_name ) }}
            </b-dropdown-item>
        </b-dropdown>
    </section>
</template>
<script>
    
    import {
        capitalizeWords,
    } from '../../../../functions';
    
    import { useSessionStore } from '../../../../stores/session';

    export default {
        setup(){
            const storeSession = useSessionStore();
            return { storeSession };
        },
        props: {
            dataParticipantsUser: {
                type: Object,
                required: true,
            },
        },
        data() {
            return {
                company: new Object(),
            }
        },
        computed: {
            isEnabledSelectorCompany(){
                return Object.keys( this.dataParticipantsUser ).length > 1;
            },
        },
        created(){

            this.company = _.cloneDeep( this.storeSession.company );
            this.participant = _.cloneDeep( this.storeSession.participant );
        },
        methods: {
            capitalizeWords, 
        }
    }
</script>

<style lang="scss" scoped>
    @use '../../../../../sass/variables' as var;

    .company-content{
        .name-entity{
            color: var.$color-dark-teal;
            font-weight: 800;
        }
        .dropdown-account{
            .dropdown-trigger{
                .button{
                    border: none;
                    color: var.$color-warning;
                    top: -0.68rem;
                    .icon{
                        font-size: 1.7rem;
                    }
                }
            }
            .dropdown-menu{
                .dropdown-item{
                    font-weight: 700;
                }
                .dropdown-item.is-current{
                    cursor: none;
                    font-style: italic;
                }
            }
        }
    }
</style>