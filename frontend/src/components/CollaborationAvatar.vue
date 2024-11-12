<template>
        
    <NoData v-if="!avatar.avatar_id" text="No information available." />
    <q-card v-else class="w-100 q-my-xs q-mx-sm-xs" flat>

        <!-- Meta -->
        <q-card-section class="row">
            <div class="col-12 col-sm q-py-xs">
                <span class="text-overline row">
                    {{ user && user.name && avatar.age
                        ? user.name + ', ' + avatar.age 
                        : user && user.name
                            ? user.name
                            : 'Collaborator' }}
                </span>
                <p class="text-h5 row">
                    Current Position
                </p>
                <span class="text-caption text-grey row q-pr-sm-md">
                    {{ avatar.about }}
                </span>
            </div>
            <div class="col-12 col-sm-auto q-pt-md q-pt-sm-none">
                <div class="flex justify-center w-100">
                    <q-img 
                        v-if="user.avatar" 
                        width="160px"
                        :src="user.avatar" 
                        :ratio="1" 
                        alt="user-avatar"
                        img-class="banner-image"
                        fit="fill" 
                        loading="eager"
                    />
                </div>
                <!-- div class="flex justify-center q-gutter-sm q-my-sm">
                    <q-btn 
                        outline
                        round
                        size="sm" 
                        color="purple" 
                        icon="bookmark_added" 
                        :disable="!$user.access.user" 
                        @click="$emit('connect')" 
                    />
                </div> -->
            </div>
        </q-card-section>

        <!-- Impressum & Redirects -->
        <q-separator />
        <q-card-section class="row">
            <div class="col-12 col-sm text-caption q-py-xs">
                <span class="text-caption">
                    <b>Location:</b><br>
                    {{ avatar.location && avatar.location.id
                        ? avatar.location.area + ', ' + avatar.location.country 
                        : 'Set to private.'
                    }}
                </span>
                <div class="text-caption">
                    <span><b>Languages:</b></span><br>
                    <span v-if="avatar.language && avatar.language.length === 0">No data available.</span>
                    <span 
                        v-for="(language, languageIndex) in avatar.language" 
                        :key="languageIndex" 
                    >
                        {{ avatar.language[languageIndex + 1] ? language.name + ', ' : language.name }}
                    </span>
                </div>
            </div>
            <div class="col-grow q-py-xs text-right flex _text-break">
                <div class="w-100 text-caption text-end">
                    <span><b>Contact details</b></span><br>
                    <span >{{ avatar.contact ? avatar.contact : 'Set to private.' }}</span>
                </div>
            </div>
        </q-card-section>
    </q-card>
    
</template>

<script>
export default {
    name: 'CollaborationAvatar',
    components: {
        // 
    },

    props: {
        avatar: Object,
        user: Object,
        noDataMessage: String
    },
};
</script>
