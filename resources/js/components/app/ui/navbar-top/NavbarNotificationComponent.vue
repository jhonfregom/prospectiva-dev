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
    @use '../../../../../sass/colors' as colors;

    .notify-content{
        color: colors.$color-secondary;
        position: relative;
        top: -0.3rem;
    }
</style>