'use strict';
import store from "src/stores/user.js";

const routesBackpanel = [
    {
        path: '/admin/dashboard',
        name: 'AdminBackpanel',
        component: () => import('src/pages/admin/AdminBackpanel.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    }, {
        path: '/admin/newsfeed',
        name: 'AdminNewsfeed',
        component: () => import('src/pages/admin/AdminNewsfeed.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    },
];

export default routesBackpanel;
