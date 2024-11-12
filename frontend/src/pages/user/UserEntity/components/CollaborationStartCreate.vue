<template>

    <!-- Collaboration Definitions -->
    <div class="row w-100 justify-center">
        <div class="w-card">

            <!-- Type -->
            <CardSimple title="Reward type">
                <q-card-section>
                    <q-select v-model="reward.award" :options="awards" label="Select reward" />
                </q-card-section>
                <q-separator />
                <q-card-section>
                    <span class="text-caption"><b>Definition:</b>&nbsp;{{ reward.award?.description ?? 'Please select award.' }}</span>
                </q-card-section>
            </CardSimple>

            <!-- Participation details -->
            <CardSimple 
                title="Rewarded skills" 
                tooltip="Define skills which will be rewarded to collaborator."
                tooltipIconColor="primary"
            >
                <template #actions>
                    <q-btn 
                        label="Add" 
                        icon="add"
                        color="primary" 
                        size="sm"  
                        @click="addSkilltoList(newSkillDefinition.label, newSkillDefinition.description)"
                    />
                </template>
                <q-card-section>
                    <q-input v-model="newSkillDefinition.label" label="Label" />
                    <q-input
                        label="Definition"
                        v-model="newSkillDefinition.description"
                        maxlength="199"
                        type="textarea"
                        counter autogrow
                    />
                </q-card-section>
                <q-separator />
                <q-list separator>
                    <q-item>
                        <q-item-section>
                            <q-item-label overline>Definitions:</q-item-label>
                        </q-item-section>
                    </q-item>
                    <q-item v-if="reward.skills.length === 0">
                        <q-item-section>
                            <q-item-label caption>No skills rewarded.</q-item-label>
                        </q-item-section>
                    </q-item>
                    <q-item 
                        v-else
                        v-for="(skill, index) in reward.skills"
                        :key="index" 
                        clickable v-ripple
                    >
                        <q-item-section>
                            <q-item-label>{{ skill.label }}</q-item-label>
                            <q-item-label caption>{{ skill.description }}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                            <q-btn 
                                icon="delete" 
                                color="red" 
                                dense 
                                size="xs" 
                                @click="reward.tasks.splice(index, 1)"
                            />
                        </q-item-section>
                    </q-item>
                </q-list>
            </CardSimple>
        </div>

        <!-- Collaboration -->                
        <div class="w-card-xl">
            <CardSimple title="Reward definition">
                <q-card-section>
                    <q-input v-model="reward.title" label="Title" />
                    <q-input v-model="reward.meta" label="Meta" />
                    <q-input 
                        v-model="reward.about" 
                        label="About" 
                        type="textarea" 
                        counter 
                        maxlength="199" 
                    />
                </q-card-section>
            </CardSimple>
        </div>

        <!-- Navigation -->
        <div class="row w-100 q-ma-md flex justify-end">
            <slot name="navigation" />
        </div>
    </div>

</template>

<script>
import { ref } from 'vue'

export default {
    name: 'CollaborationStartCreate',

    props: {
        awards: Array,
        modelValue: {
            type: Object,
            required: true
        }
    },

    computed: {
        reward: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },

    emtis: [
        'update:modelValue'
    ],

    setup() {
        const newSkillDefinition = ref({
            label: '',
            description: '',
        })
        
        return {
            newSkillDefinition,
        };
    },

    data() {
        return {
            // 
        }
    },

    methods: {
        addSkilltoList(label, description) {

            if(this.reward.skills.length >= 2) {
                this.$toast.error('You are able to define up to 9 skills.')
                return;
            }

            if(!label || !description) 
                return;
            
            this.reward.skills.push({
                label: label,
                description: description
            });

            this.newSkillDefinition.label = '';
            this.newSkillDefinition.description = '';
        },
    },
};
</script>
