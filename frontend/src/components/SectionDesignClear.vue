<template>

    <div class="w-100 flex justify-center q-py-xl"
        :class="{
            'bg-dark': $q.dark.isActive,
            'bg-grey-1': !$q.dark.isActive,
            'text-white': $q.dark.isActive,
            'text-dark': !$q.dark.isActive,
        }"
    >
        <div class="row justify-center w-content">
            <slot />
        </div>
    </div>
    
</template>

<script>
export default {
    name: 'SectionDesignClear',
    props: {
        // 
        
    },
};
</script>
