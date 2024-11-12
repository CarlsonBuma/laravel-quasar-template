<template>

    <div class="flex items-center">
        <slot name="prepend"/>
        <q-btn class="q-mx-xs" dense size="xs" outline icon="update" color="green">
            <q-popup-edit :modelValue="areaIndex" class="row" v-slot="scope">
                <q-input label="Keyword" v-model="area.label" dense autofocus />
                <q-checkbox v-model="area.is_public" />
                <q-btn 
                    dense 
                    outline 
                    icon="update" 
                    color="green" 
                    @click="changeKeyword(area.label, area.keyword_id, area.is_public, area.departement_id, scope)"
                />
            </q-popup-edit>
        </q-btn>
        <q-btn class="q-mx-xs" dense size="xs" outline icon="delete" color="red" @click="confirmDelete(keyword.keyword_id, group, keywordIndex)"/>     
    </div>
    
</template>

<script>
export default {
    name: 'DataHandler',
    props: {
        text: String,
        color: String,
        noText: Boolean,
    },
};
</script>
