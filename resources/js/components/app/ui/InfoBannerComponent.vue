<template>
    <b-message type="is-info" has-icon>
        <div class="info-banner-content">
            <div class="info-description" v-html="formatDescription">
            </div>
            <div v-if="editsInfo" class="edits-info">
                <strong>Ediciones disponibles:</strong> {{ editsInfo }}
            </div>
            <div v-if="instructions" class="instructions">
                <strong>Instrucciones:</strong> {{ instructions }}
            </div>
        </div>
    </b-message>
</template>

<script>
export default {
    name: 'InfoBannerComponent',
    props: {
        description: {
            type: String,
            required: true
        },
        editsInfo: {
            type: String,
            default: null
        },
        instructions: {
            type: String,
            default: null
        }
    },
    computed: {
        formatDescription() {
            return this.description;
        }
    }
}
</script>

<style lang="scss" scoped>
.info-banner-content {
    .info-description {
        margin-bottom: 8px;
        line-height: 1.5;
        text-align: justify;
    }
    
    .edits-info {
        margin-bottom: 8px;
        color: #2c5aa0;
        font-size: 0.95rem;
    }
    
    .instructions {
        color: #2c5aa0;
        font-size: 0.95rem;
        line-height: 1.4;
    }
}
</style>