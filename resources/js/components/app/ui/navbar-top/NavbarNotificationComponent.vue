<template>
    <section class="notify-content">
        <b-icon
            pack="far"
            icon="bell"
            size="is-medium"
            />
    </section>
</template>
<script>
    export default {

    }
</script>

<style lang="scss" scoped>
    @use '../../../../../sass/variables' as var;

    .notify-content{
        color: var.$color-warning;
        position: relative;
        top: -0.3rem;
    }
</style>