<template>

    <div class="row">
        <div class="col-auto">
            <slot name="left" />
        </div>
        <div class="col-grow flex items-center text-center">
            <slot name="right" />
        </div>
    </div>
    
</template>

<script>
export default {
    name: 'SectionSplitFix',
};
</script>
