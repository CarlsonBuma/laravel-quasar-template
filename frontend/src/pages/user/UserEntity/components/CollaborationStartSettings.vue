<template>

    <!-- Collaboration Definitions -->
    <div class="row w-100 justify-center">
        <div class="w-card-lg">

            <!-- Access -->
            <CardSimple 
                title="Public access" 
                tooltip="Private collaborations are only accessible by private token via link."
                tooltipIconColor="primary"
            >
                <template #actions>
                    <q-toggle 
                        class="q-ml-md q-mr-sm"  
                        v-model="settings.public_access.is_public" 
                        dense 
                    />
                </template>
                <q-list separator>
                    <!-- SEO -->
                    <q-item v-if="settings.public_access.is_public">
                        <q-item-section>
                            <q-select 
                                clearable use-input use-chips dense
                                multiple counter hide-dropdown-icon
                                label="SEO tags"
                                hint="Choose up to 9 tags."
                                v-model="settings.public_access.tags"
                                max-values="9"
                                input-debounce="0"
                                new-value-mode="add-unique"
                                @clear="settings.public_access.tags = []"
                            />
                        </q-item-section>
                    </q-item>
                </q-list>
            </CardSimple>

            <!-- Limits -->
            <CardSimple 
                title="Limit of active members" 
                tooltip="Limit amount of active collaborators taking part within collaboration."
                tooltipIconColor="primary"
            >
                <q-list separator>
                    <q-item>
                        <q-item-section>
                            <q-input 
                                v-model="settings.public_access.access_limit" 
                                dense type="number" 
                                label="Limit:" 
                                hint="Amount of active collaborators. Set '0' for no limit." 
                            />
                        </q-item-section>
                    </q-item>
                </q-list>
            </CardSimple>
        </div>

        <!-- Collaboration -->                
        <div class="w-card-lg">
            <CardSimple title="Onboarding">
                <q-list  separator>
                    <q-item>
                        <q-item-section>
                            <q-item-label>
                                Form of release
                            </q-item-label>
                            <q-item-label caption>
                                Notify collaborator after release! 
                                Provide information, that guide to a successful start of collaboration -
                                let collaborators know, how to proceed.
                            </q-item-label>
                        </q-item-section>
                        <q-item-section avatar>
                            <q-toggle 
                                v-model="settings.request_form.required" 
                                dense
                            />
                        </q-item-section>
                    </q-item>
                </q-list>

                <!-- Form Meta -->
                <q-separator />
                <q-list v-if="settings.request_form.required" separator>
                    <q-item >
                        <q-item-section>
                            <q-item-label caption><b>To:</b> released.collaborator@email.io</q-item-label>
                        </q-item-section>
                    </q-item>
                    <q-item >
                        <q-item-section>
                            <q-input 
                                v-model="settings.request_form.label" 
                                label="Contact details" 
                                type="textarea"
                                hint="Let collaborators know, how they should contact you." 
                            />
                        </q-item-section>
                    </q-item>

                    <!-- Manage Attachement -->
                    <q-item>
                        <q-item-section>
                            <q-item-label>Attachement:</q-item-label>
                            <q-item-label caption>Required documents (.pdf only)</q-item-label>
                        </q-item-section>
                        <q-item-section avatar>
                            <q-btn 
                                size="sm" 
                                label="Add" 
                                color="primary" 
                                icon="add" 
                                :disable="settings.request_form.attachement_labels.length > 4"
                                @click="settings.request_form.attachement_labels.push('')"
                            />
                        </q-item-section>
                    </q-item>
                    
                    <!-- Attachements -->
                    <q-item v-for="(attachement_labels, index) in settings.request_form.attachement_labels" :key="index">
                        <q-item-section>
                            <q-input 
                                dense 
                                label="File label"
                                :model-value="attachement_labels" 
                                @update:model-value="(value) => settings.request_form.attachement_labels[index] = value" 
                            >
                                <template v-slot:append>
                                    <q-icon color="primary" name="upload" />
                                </template>
                            </q-input>
                        </q-item-section>
                        <q-item-section side>
                            <q-btn 
                                icon="delete" 
                                color="red" 
                                dense 
                                size="xs" 
                                @click="settings.request_form.attachement_labels.splice(index, 1)"
                            />
                        </q-item-section>
                    </q-item>
                </q-list>
            </CardSimple>
        </div>

        <!-- Navigation -->
        <div class="row w-100 q-ma-md flex justify-end">
            <slot name="navigation" />
        </div>
    </div>

</template>

<script>
export default {
    name: 'BusinessCollaborationSettings',
    components: {
        //
    },

    props: {
        modelValue: {
            type: Object,
            required: true
        }
    },

    computed: {
        settings: {
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
        return {
            // 
        }
    },

    data() {
        return {
            // 
        }
    },

    methods: {
        // 
    },
};
</script>
