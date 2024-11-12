'use strict';
import { defineStore } from "pinia";
import { LocalStorage } from 'quasar'
import axios from 'axios';

const storeUser = defineStore({
    id: "user",
    state: () => ({
        access: {
            bearer_token: false,    // Bearer to authorize user
            user: false,            // Access to memberarea
            admin: false,           // Access to adminarea 
            tokens: [],             // ['access-token': 'expiration_date']
        },
        user: {
            id: 0,
            name: 'User',
            img_src: '',
            email: ''
        },
        avatar: {
            id: 0,
            is_community: false,
        },
        entity: {
            id: 0,
            is_community: false,
        }
    }),
    
    actions: {

        // ************************
        // User Access
        // ************************
        setUser(user, avatar, entity, isAdmin, businessCockpit) {
            // Static
            this.user = user;
            this.avatar = avatar;
            this.entity = entity;

            // Access
            this.access.user = true;
            this.access.admin = isAdmin
            this.setUserAccess(
                businessCockpit.access_token, 
                businessCockpit.expiration_date
            );
        },

        setUserAccess(accessToken, expirationDate) {
            if(!accessToken || !expirationDate) return;
            this.access.tokens[accessToken] = {
                expiration_date: expirationDate,
                access_token: accessToken
            }
        },

        removeSubscriptionAccess(accessToken) {
            this.access.tokens[accessToken] = null;
        },

        removeAdmin() {
            this.access.admin = false;
        },

        // ****************************
        // User Session
        // ****************************
        setSessionToken(sessionToken) {
            LocalStorage.set(process.env.SESSION_NAME, sessionToken)
        },

        checkSessionToken() {
            return LocalStorage.getItem(process.env.SESSION_NAME)
                ? true
                : false
        },

        removeSessionToken() {
            LocalStorage.remove(process.env.SESSION_NAME)
        },

        setSession() {
            const token = LocalStorage.getItem(process.env.SESSION_NAME);
            if(token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
                this.access.bearer_token = true;
            };
        },

        removeSession() {
            axios.defaults.headers.common['Authorization'] = '';
            this.access.bearer_token = false
            this.access.user = false;
            this.access.admin = false;
            this.access.tokens = [];
            this.user = {};
            this.avatar = {};
            this.entity = {};
        },
    }
});

export default storeUser;
