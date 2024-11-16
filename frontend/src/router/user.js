'use strict';
import store from "src/stores/user.js";

const routesUser = [
    {
        path: "/avatar/profile",
        name: "UserProfile",
        component: () => import('src/pages/user/UserProfile.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    }
];

export default routesUser;
