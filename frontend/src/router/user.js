'use strict';
import store from "src/stores/user.js";

const routesUser = [
    {
        path: "/my-avatar",
        name: "UserDashboard",
        component: () => import('src/pages/user/UserAvatar/UserDashboard.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    }, {
        path: "/my-avatar/rewards",
        name: "UserRewards",
        component: () => import('src/pages/user/UserAvatar/UserRewards.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    }, {
        path: "/shortcuts/entities",
        name: "EntityShortcuts",
        component: () => import('src/pages/user/UserShortcuts/EntityShortcuts.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    }, {
        path: "/my-avatar/skillset",
        name: "UserSkillSet",
        component: () => import('src/pages/user/UserAvatar/UserSkillSet.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    },

    //* User App Access
    {
        path: "/account/access",
        name: "UserAccess",
        component: () => import('src/pages/auth/UserAccess.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    },

    //* User Account
    {
        path: "/my-avatar/profile",
        name: "UserProfile",
        component: () => import('src/pages/auth/UserProfile.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    }, {
        path: "/account/settings",
        name: "UserAccount",
        component: () => import('src/pages/auth/UserAccount.vue'),
        beforeEnter: (to, from, next) => {
            if (!store().access.user) next('/');
            else next();
        },
    },

    //* User Account Access
    {
        path: "/create-account",
        name: "CreateNewAccount",
        component: () => import('src/pages/auth/CreateNewAccount.vue'),
    }, {
        path: "/password-reset-request",
        name: "PasswordResetRequest",
        component: () => import('src/pages/auth/PasswordResetRequest.vue'),
    }, {
        path: "/password-reset/:email/:key",
        name: "PasswordReset",
        component: () => import('src/pages/auth/PasswordReset.vue'),
    }, {
        path: "/email-verification-request/:email",
        name: "EmailVerificationRequest",
        component: () => import('src/pages/auth/EmailVerificationRequest.vue'),
    },
    {
        path: "/email-verification/:email/:key",
        name: "EmailVerification",
        component: () => import('src/pages/auth/EmailVerification.vue'),
    }, {
        path: "/transfer-account/:email/:key/:transfer",
        name: "TransferAccount",
        component: () => import('src/pages/auth/TransferAccount.vue'),
    }, {
        path: "/login",
        name: "UserLogin",
        component: () => import('src/pages/auth/UserLogin.vue'),
    }
];

export default routesUser;
