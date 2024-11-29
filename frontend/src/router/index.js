'use strict';
import { route } from 'quasar/wrappers';
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router';
import routesVisitors from './visitors';
import routesAuth from './auth';
import routesUser from './user';
import routesCockpit from './cockpit';
import routesBackpanel from './admin';

export default route(function (/* { store, ssrContext } */) {
    const createHistory = process.env.SERVER
        ? createMemoryHistory
        : (process.env.APP_VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory);

    // Default-Routes
    const routes = [{
        path: '/:catchAll(.*)*',
        component: () => import('pages/ErrorNotFound.vue')
    }];

    // Routes
    routes.push(...routesVisitors)
    routes.push(...routesAuth);
    routes.push(...routesUser);
    routes.push(...routesCockpit);
    routes.push(...routesBackpanel);

    // Init Router
    const Router = createRouter({
        scrollBehavior: () => ({ left: 0, top: 0 }),
        routes,
        history: createHistory(process.env.VUE_ROUTER_BASE)
    });

    return Router;
})
