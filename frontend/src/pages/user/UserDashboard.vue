<style lang="sass" scoped>
.column-default-width
    min-width: 220px
.column-first-width
    min-width: 280px
</style>

<template>

    <PageWrapper >
        <template #navigation>
            <NavUser />
        </template>

        <div class="flex w-100 _flex-no-wrap">
            <div class="container">

                <!-- 1. Row / Header -->
                <div class="row no-wrap flex">

                    <!-- TH - Table Handling (x & y) -->
                    <div class="column-first-width">
                        <CardSimple>
                            <q-card-section class="text-right">
                                <q-btn 
                                    label="Add new role"
                                    icon="add" 
                                    size="sm"  
                                    color="primary"
                                    @click="addNewColumn(newRole)" 
                                />
                            </q-card-section>
                            <q-card-section class="text-left">
                                <q-btn 
                                    label="Add workflow"
                                    icon="add" 
                                    size="sm"  
                                    color="primary"
                                    @click="addNewRow(newWorkflow, roles)" 
                                />
                            </q-card-section>
                        </CardSimple>
                    </div>
                    
                    <!-- TH - Roles as Columns -->
                    <div class="column-default-width" v-for="(role, index) in roles" :key="index">
                        <CardSimple >
                            <q-list class="q-pb-none">
                                <q-item>
                                    <q-item-section>
                                        <q-item-label caption>Role #{{ index + 1 }}</q-item-label>
                                    </q-item-section>
                                    <q-item-section class="text-caption" side top>
                                        <q-btn icon="delete" size="6px" @click="roles.splice(index, 1)" dense color="red"/>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                            <q-separator />
                            <q-card-section class="q-pa-sm">
                                <q-input dense placeholder="Name role" v-model="role.title" />
                            </q-card-section>
                        </CardSimple>
                    </div>
                </div>

                <!-- Rows N -->
                <q-separator class="q-my-sm w-100" />
                <div class="row no-wrap" v-for="(workflow, indexWorkflow) in workflows" :key="indexWorkflow">
                    
                    <!-- Default Column / Workflows -->
                    <div class="column-first-width">
                        <CardSimple>
                            <q-list>
                                <q-item>
                                    <q-item-section>
                                        <q-item-label>Flow #{{ indexWorkflow + 1 }}</q-item-label>
                                    </q-item-section>
                                    <q-item-section class="text-caption" side top>
                                        <q-btn icon="delete" size="6px" @click="workflows.splice(indexWorkflow, 1)" dense color="red"/>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                            <q-separator />
                            <q-card-section>
                                <q-input 
                                    autogrow 
                                    dense 
                                    maxlength="199" 
                                    type="textarea" 
                                    placeholder="Define workflow" 
                                    v-model="workflow.definition" 
                                />

                                <!-- q-item-section class="text-caption" side top>
                                    <span>Total time: 12h</span>
                                    <span>Quality Points: 3</span>
                                    <span>Complexity: 5</span>
                                    <span>Roles: 3</span>
                                </q-item-section> -->
                            </q-card-section>
                        </CardSimple>
                    </div>

                    <div class="column-default-width q-pa-md" v-for="(role, indexRole) in workflow.roles" :key="indexRole">
                        <div class="text-center w-100">
                            <q-btn 
                                icon="add" 
                                round outline 
                                color="primary" 
                                @click="workflows[indexWorkflow].roles[indexRole].linked = !workflows[indexWorkflow].roles[indexRole].linked"
                            /><br>
                            <span class="text-caption">X-Role: {{ indexRole }}</span><br>
                            <span class="text-caption">Y-Workflow: {{ indexWorkflow }} </span><br>
                            <span class="text-caption">Linked: {{ role.linked }} </span>
                        </div> 
                    </div>                        
                </div>
            </div>
        </div>
    </PageWrapper>

</template>

<script>
import { ref } from 'vue';

export default {
    name: 'UserDashboad',
    components: {
        // Components
    },

    setup() {
        return {
            
            newRole: ref({
                title: '',
                linked: false
            }),
            
            newWorkflow: ref({
                definition: '',
                roles: []
            }),
            
            workflows: ref([]),     // Rows  
            roles: ref([])          // Columns
        }
    },

    data() {
        return {
            // Code
        }
    },

    mounted() {
        // Code
    },

    methods: {
        addNewRow(workflow, roles) {
            workflow.roles = roles
            this.workflows.push(workflow)
            this.newWorkflow = {
                definition: '',
                roles: []
            };

            console.log(this.workflows)
        },

        addNewColumn(role) {
            this.roles.push(role)
            this.newRole = {
                title: '',
                linked: false
            };
        }
    }
};
</script>
