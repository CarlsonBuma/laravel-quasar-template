'use strict';

const routesCommunity = [
    
    //* Collaborators
    {
        path: "/community/collaborations",
        name: "WebCommunityCollaborations",
        component: () => import('src/pages/community/WebCommunityCollaborations.vue'),
    },
    
    {
        path: "/community/services",
        name: "WebCommunityServices",
        component: () => import('src/pages/community/WebCommunityServices.vue'),
        props: (route) => ({
            keywords: Array.isArray(route.query.keywords) 
                ? route.query.keywords 
                : [route.query.keywords]
        })
    }, {
        path: "/community/collaborators/:avatar_id",
        name: "WebCommunityAvatar",
        component: () => import('src/pages/community/WebCommunityAvatar.vue'),
    }, {
        path: "/community/entities/:entity_id",
        name: "WebCommunityEntity",
        component: () => import('src/pages/community/WebCommunityEntity.vue'),
    },

    //* Landingpage
    {
        path: '/',
        redirect: '/landingpage'
    }, {
        path: '/landingpage',
        name: 'LandingPage',
        component: () => import('src/pages/guest/LandingPage.vue'),
    }, {
        path: '/awards',
        name: 'WebAwards',
        component: () => import('src/pages/guest/WebAwards.vue'),
    }, {
        path: "/newsfeed",
        name: "WebReleases",
        component: () => import('src/pages/guest/WebReleases.vue'),
    }, {
        path: "/about",
        name: "WebAbout",
        component: () => import('src/pages/guest/WebAbout.vue'),
    }, {
        path: "/pricing",
        name: "WebPricing",
        component: () => import('src/pages/guest/WebPricing.vue'),
    }, {
        path: "/legal",
        name: "WebLegal",
        component: () => import('src/pages/guest/WebLegal.vue'),
    }
];

export default routesCommunity;
