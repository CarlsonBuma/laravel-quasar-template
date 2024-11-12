<template>

    <q-list>
        <!-- Matching skills-->
        <q-expansion-item expand-separator icon="engineering" label="Matching skills">
            <NoData v-if="matchingSkills.length === 0" :loading="loading" text="No matches found."/>
            <q-list v-else>
                <q-item lines="1" v-for="(skill, index) in matchingSkills" :key="index" clickable v-ripple>
                    <q-item-section avatar>
                        o
                    </q-item-section>
                    <q-item-section>{{ skill.label }}</q-item-section>
                </q-item>
            </q-list>
        </q-expansion-item>

        <!-- Recommended skills -->
        <q-expansion-item expand-separator icon="assistant" label="Listed skills" default-opened>
            <NoData v-if="recommendedSkills.length === 0" :loading="loading" text="No skills available."/>
            <q-list v-else>
                <q-item v-for="(skill, index) in recommendedSkills" :key="index" clickable v-ripple>
                    <q-item-section avatar>
                        o
                    </q-item-section>
                    <q-item-section>{{ skill.label }}</q-item-section>
                </q-item>
            </q-list>
        </q-expansion-item>
    </q-list>

</template>

<script>


export default {
    name: 'CollaborationSkills',
    components: {
        // 
    },

    props: {
        loading: Boolean,
        recommendedSkills: Array,
        matchingSkills: Object
    },

    emits: [
        'search',
        'reset'
    ],
};
</script>
