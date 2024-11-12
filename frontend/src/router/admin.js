'use strict';
import store from "src/stores/user.js";

const routesBackpanel = [
    {
        path: '/backpanel',
        name: 'AdminBackpanel',
        component: () => import('src/pages/admin/AdminBackpanel.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    }, {
        path: '/releasemanagement',
        name: 'ReleaseManagement',
        component: () => import('src/pages/admin/ReleaseManagement.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.admin) next('/');
            else next();
        }
    },
];

export default routesBackpanel;
