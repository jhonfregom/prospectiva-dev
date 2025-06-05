<script>
    export default {

    }
</script>
<template>
    <section class="notify-content">
        <b-icon
            pack="far"
            icon="bell"
            size="is-medium"
            />
    </section>
</template>
<style lang="scss" scoped>
    @use '../../../../../sass/variables' as var;

    .notify-content{
        color: var.$color-warning;
        position: relative;
        top: -0.3rem;
    }
</style>
