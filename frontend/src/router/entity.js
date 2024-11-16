'use strict';
import store from "src/stores/user.js";

const accessToCockpit = process.env.APP_ACCESS_COCKPIT
const fallBackRoute = 'LandingPage';

/**
 * 
 */
const routesEntity = [
    {
        path: '/entity/profile',
        name: 'EntityProfile',
        component: () => import('src/pages/entity/EntityProfile.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next({ name: fallBackRoute });
            else if (!store().access.tokens[accessToCockpit]) next({ name: fallBackRoute });
            else next();
        },
    },
];

export default routesEntity;
