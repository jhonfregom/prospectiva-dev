<template>
    <div class="column is-9 is-hidden-mobile is-hidden-tablet-only is-size-7-tablet contentMenu">
        <ul class="listMenu">
            <li v-for="(item, index) in items"
                :key="index"
                :class="{ listMenuActive: item.active }"
                v-on:click="handleClick(item.action)">
                <a href="#">
                    {{ item.label }}
                </a>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
    props: {
        items : {
            type: Array,
            required: true,
            validator: (items) => {
                
                return items.every(
                    (item) => item.label && typeof item.active === 'boolean' && typeof item.action === 'function'
                )
            }
        }
    },
    methods: {
        handleClick(action) {
            if( typeof action === 'function' ) {
                action();
            }else{
                console.warn('Action is not a function');
            }
        }
    },
    data() {
        return {
            menu: [
                {
                    title: 'Variables',
                    icon: 'format-list-bulleted',
                    route: '/variables'
                },
                {
                    title: 'Matriz de Influencia',
                    icon: 'grid',
                    route: '/matriz'
                }
            ]
        }
    }
}
</script>

<style lang="scss" scoped>
    @use '../../../../sass/colors' as colors;

    .listMenu {
        text-align: center;

        li {
            border: 1px solid colors.$color-accent;
            border-radius: 15px;
            margin-bottom: 5px;
            padding: 2px;

            a {
                color: colors.$color-accent;
            }
        }
    }

    .listMenuActive {
        background-color: colors.$color-accent;

        a {
            color: white !important;
        }
    }
</style>