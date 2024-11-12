<template>

    <div class="container">
        <div class="row q-py-md">
            <span class="col-12 text-right text-caption"><b>Type:</b><br> {{ collaboration.award?.label ?? 'Individual' }}</span>
        </div>
        
        <!-- Definition -->
        <span class="text-h6 row q-mt-sm">{{ collaboration.title ? collaboration.title : 'Title' }}</span>
        <span class="row">{{ collaboration.meta ? collaboration.meta : '' }}</span>
        
        <!-- Definition -->
        <div class="row q-py-sm q-mt-sm">
            <span class="col-12 text-overline">About:</span>
            <span class="col-12 text-caption">{{ collaboration.about ? collaboration.about : 'No information provided.' }}</span>
        </div>

        <!-- Details -->
        <div class="row q-py-sm" v-if="collaboration.content">
            <span class="col-12 text-overline">Details:</span>
            <q-editor 
                content-class="text-caption q-pa-none"
                min-height="0rem"
                dense readonly flat 
                :toolbar="[]" 
                :model-value="collaboration.content" 
            />
        </div>

        <div class="q-py-sm" v-if="collaboration.skills.length > 0">
            <span class="text-overline">Rewarded skills:</span>
            <ul class="q-ma-none text-caption">
                <li v-for="(skill, index) in collaboration.skills" :key="index">
                    <b>{{ skill.label }}:</b> {{ skill.description }} 
                </li>
            </ul>
        </div>

        <q-separator class="q-my-md" />
        <div class="w-100 text-right text-caption">
            <span><b>Contact details:</b></span><br>
            <span class="_text-break">{{ collaboration.contact ? collaboration.contact : 'No information provided.' }}</span>
        </div>
        
        <!--<div class="text-caption row">
            <div class="col-6 _text-break">
                <span v-if="entity.name">{{ entity.name }}</span>
                <span v-if="entity.contact"><br>{{ entity.contact }}</span>
                <span v-if="entity.website"><br>{{ entity.website }}</span>
            </div>
        </div>-->
    </div>
</template>

<script>
export default {
    name: 'CollaborationPreview',
    props: {
        allowActions: Boolean,
        collaboration: Object,
        entity: Object,
    },

    emits: [
        'participate'
    ]
};
</script>
