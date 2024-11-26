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
        path: '/admin/access',
        name: 'AccessManagement',
        component: () => import('src/pages/admin/AccessManagement.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    }, {
        path: '/admin/newsfeed',
        name: 'NewsfeedManagement',
        component: () => import('src/pages/admin/NewsfeedManagement.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    },
];

export default routesBackpanel;
