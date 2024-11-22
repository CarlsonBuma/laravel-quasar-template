'use strict';
import store from "src/stores/user.js";

const accessToCockpit = process.env.APP_ACCESS_COCKPIT
const fallBackRoute = '/';

const routesEntity = [
    {
        path: '/entity/dashboard',
        name: 'EntityDashboard',
        component: () => import('src/pages/entity/EntityDashboard.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next(fallBackRoute);
            else if (!store().access.tokens[accessToCockpit]) next(fallBackRoute);
            else next();
        },
    }, {
        path: '/entity/profile',
        name: 'EntityProfile',
        component: () => import('src/pages/entity/EntityProfile.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next(fallBackRoute);
            else if (!store().access.tokens[accessToCockpit]) next(fallBackRoute);
            else next();
        },
    },
];

export default routesEntity;
