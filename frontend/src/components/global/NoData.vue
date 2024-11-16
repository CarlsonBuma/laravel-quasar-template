<template>

    <div class="row w-100 text-center q-py-md">
        <div class="col-12 q-mb-sm">
            <q-icon name="data_exploration" size="72px" :color="color ? color : 'primary'" />
        </div>
        <div class="col-12" v-if="!noText">
            <span :class="'text-' + color" class="text-caption">{{ text }}</span>
        </div>
    </div>
    
</template>

<script>
export default {
    name: 'NoData',
    props: {
        text: String,
        color: String,
        noText: Boolean,
    },
};
</script>
