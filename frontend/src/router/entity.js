'use strict';
import store from "src/stores/user.js";

const accessTokenBusinessCockpit = process.env.APP_ACCESS_access_cockpit
const fallBackRoute = 'LandingPage';

const routesEntity = [

    //* Collaborations
    {
        path: '/my-entity',
        name: 'BusinessDashboard',
        component: () => import('src/pages/user/UserEntity/BusinessDashboard.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next({ name: fallBackRoute });
            else if (!store().access.tokens[accessTokenBusinessCockpit]) next({ name: fallBackRoute });
            else next();
        },
    }, {
        path: "/my-entity/start-collaboration/:award_id?",
        name: "BusinessCollaborationStart",
        component: () => import('src/pages/user/UserEntity/BusinessCollaborationStart.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next({ name: fallBackRoute });
            else if (!store().access.tokens[accessTokenBusinessCockpit]) next({ name: fallBackRoute });
            else next();
        },
    }, 
    
    //* Legal stuff
    {
        path: '/my-entity/impressum',
        name: 'BusinessProfile',
        component: () => import('src/pages/user/UserEntity/BusinessImpressum.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next({ name: fallBackRoute });
            else if (!store().access.tokens[accessTokenBusinessCockpit]) next({ name: fallBackRoute });
            else next();
        },
    },
];

export default routesEntity;
