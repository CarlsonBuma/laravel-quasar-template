<template>

    <NoData v-if="!collaborations || collaborations.length === 0" text="No information available."/>
    <CardSimple v-else>
        <q-tabs v-model="tabPanel" class="w-100">
            <q-tab 
                v-for="(reward, index) in collaborations" 
                :key="index"
                :icon="reward.award.icon"
                :name="index" 
            />
        </q-tabs>
        <q-tab-panels class="w-100 bg-transparent" v-model="tabPanel" animated>
            <q-tab-panel 
                v-for="(reward, index) in collaborations" 
                :key="index"
                :name="index" 
            >
                <div>
                    <span class="text-overline">{{ reward.award.label }}</span><br>
                    <span class="text-caption">{{ reward.award.description }}</span>
                </div>
                
                <q-timeline color="secondary" class="q-px-xs">
                    <q-timeline-entry
                        v-for="(collaborator, indexItem) in reward.collaborations" 
                        :key="indexItem"
                        :icon="collaborator.date_confirmed ? reward.award.icon : 'history'"
                        :color="collaborator.date_confirmed ? 'secondary' : 'orange'"
                        side="right"
                    >
                        <template #title>
                            <div class="q-mt-md">
                                <span>{{ collaborator.collaboration.title }}</span>
                            </div>
                        </template>

                        <template #subtitle>
                            <div v-if="collaborator.collaboration?.entity?.name" class="text-caption text-weight-light">
                                <span class="_hover" @click="$router.push('/community/entities/' + collaborator.collaboration.entity.id)">
                                    <em>{{ collaborator.collaboration.entity.name }}</em>
                                </span><br>
                            </div>
                            <div class="row">
                                <!-- Duration -->
                                <div class="col-grow">
                                    <div class="flex items-center">
                                        <span>Start: {{ collaborator.period_start ?? 'Individual' }}</span>
                                        
                                        <!-- Edit -->
                                        <q-icon class="q-mx-xs" v-if="allowActions" name="settings"/>
                                        <q-popup-edit class="w-popup" auto-close v-if="allowActions" v-model="collaborator.period_start" auto-save v-slot="scope">
                                            <q-input 
                                                label="Period start:"
                                                v-model="scope.value" 
                                                autofocus 
                                                type="date"
                                            >
                                                <template #append>
                                                    <q-btn outline dense size="md" icon="check" color="positive" @click="$emit('update-start', scope.value, collaborator.id)" />
                                                </template>
                                            </q-input>
                                        </q-popup-edit>
                                    </div>

                                    <div class="flex items-center">
                                        <span class="text-caption">Duration: {{ collaborator.period_duration ?? 'Ongoing' }}</span>
                                        
                                        <!-- Edit -->
                                        <q-icon class="q-mx-xs" v-if="allowActions && collaborator.date_confirmed" name="settings"/>
                                        <q-popup-edit class="w-popup" auto-close v-if="allowActions && collaborator.date_confirmed" v-model="collaborator.period_duration" auto-save v-slot="scope">
                                            <q-input 
                                                v-model="scope.value" 
                                                label="Duration:"
                                                hint="eg. 2 years, 3 months, individual etc."
                                                maxlength="99" 
                                                dense autofocus counter 
                                            >
                                                <template #append>
                                                    <q-btn outline dense size="md" icon="check" color="positive" @click="$emit('update-duration', scope.value, collaborator.id)" />
                                                </template>
                                            </q-input>
                                        </q-popup-edit>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-auto">
                                    <q-icon 
                                        v-if="allowActions"
                                        size="sm"
                                        :name="collaborator.is_public ? 'vpn_key_off' : 'vpn_key'" 
                                        :color="collaborator.is_public ? 'primary' : 'grey'"
                                    >
                                        <q-tooltip>{{ collaborator.is_public ? 'Published' : 'Private' }}</q-tooltip>
                                    </q-icon>

                                    <!-- Preview-->
                                    <q-btn 
                                        icon="visibility" 
                                        size="sm"
                                        flat round 
                                        color="purple"
                                        @click="$emit('preview', collaborator.collaboration)"
                                    />

                                    <!-- Settings -->
                                    <q-btn-dropdown v-if="allowActions" dropdown-icon="more_vert" size="sm" flat dense round>
                                        <q-list separator>
                                            <q-item>
                                                <q-item-section avatar>
                                                    <q-toggle 
                                                        dense 
                                                        v-model="collaborator.is_public"
                                                        @update:model-value="(status) => $emit('publish', status, collaborator.id)"
                                                    />
                                                </q-item-section>
                                                <q-item-section>
                                                    <q-item-label class="text-caption">Published</q-item-label>
                                                </q-item-section>
                                            </q-item>

                                            <q-item clickable v-close-popup>
                                                <q-item-section avatar>
                                                    <q-icon color="red" name="delete" />
                                                </q-item-section>
                                                <q-item-section>
                                                    <q-item-label class="text-caption" @click="confirmRemove(collaborator.id)">Delete collaboration</q-item-label>
                                                </q-item-section>
                                            </q-item>
                                        </q-list>
                                    </q-btn-dropdown>
                                </div>
                            </div>
                        </template>

                        <!-- Collaboration -->
                        <q-list class="q-mt-md">
                            <q-item class="q-pa-none">
                                <q-item-section>
                                    <q-item-label caption lines="4">
                                        {{ collaborator.collaboration.about ?? 'No information provided.'  }}
                                    </q-item-label>
                                </q-item-section>
                            </q-item>

                            <!-- Rewared Skills -->
                            <q-item class="q-px-none" v-if="collaborator.collaboration.skills?.length > 0">
                                <q-item-section>
                                    <span class="text-overline">Rewarded skills:</span>
                                    <ul class="q-ma-none text-caption">
                                        <li v-for="(skill, index) in collaborator.collaboration.skills" :key="index">
                                            <b>{{ skill.label }}:</b> {{ skill.description }} 
                                        </li>
                                    </ul>
                                </q-item-section>
                            </q-item>
                        </q-list>

                        <slot />
                    </q-timeline-entry>
                </q-timeline>
            </q-tab-panel>
        </q-tab-panels>
    </CardSimple>
   
</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CollaborationsTimeline',

    props: {
        collaborations: Array,
        allowActions: Boolean
    },

    emits: [
        'preview',
        'update-start',
        'update-duration',
        'publish',
        'delete',
    ],  

    setup() {
        const tabPanel = ref(0);
        
        return {
            tabPanel,
        }
    },

    mounted() {
        
    },

    methods: {
        confirmRemove(collaboratorID) {
            this.$q.dialog({
                title: 'Confirm delete',
                message: 'By removing participation, the current collaboration will be removed from your avatar.',
                cancel: true,
            }).onOk(() => {
                this.$emit('delete', collaboratorID)
            })
        }
    }
};
</script>
