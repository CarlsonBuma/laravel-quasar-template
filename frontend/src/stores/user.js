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
            avatar_src: '',
            email: '',
            is_public: false,
        },
    }),
    
    actions: {

        /**
         * Set user and access
         * @param {*} userID 
         * @param {*} userName 
         * @param {*} userAvatarSrc 
         * @param {*} userEmail 
         * @param {*} isAdmin 
         * @param {*} businessCockpit 
         */
        setUser(userID, userName, userAvatarSrc, userEmail, userIsPublic, isAdmin, businessCockpit) {
            
            this.user.id = userID;
            this.user.name = userName;
            this.user.avatar_src = userAvatarSrc;
            this.user.email = userEmail;
            this.user.is_public = userIsPublic;

            // Access
            this.access.user = true;
            this.access.admin = isAdmin
            this.setUserAccess(
                businessCockpit.access_token, 
                businessCockpit.expiration_date
            );
        },

        /**
         * Set app access
         * @param {*} accessToken 
         * @param {*} expirationDate 
         * @returns 
         */
        setUserAccess(accessToken = '', expirationDate = '') {
            if(!accessToken || !expirationDate) return;
            this.access.tokens[accessToken] = {
                expiration_date: expirationDate,
                access_token: accessToken
            }
        },

        /**
         * Remove app access
         * @param {*} accessToken 
         */
        removeAccess(accessToken) {
            this.access.tokens[accessToken] = null;
        },

        /**
         * Remove admin
         */
        removeAdmin() {
            this.access.admin = false;
        },

        /**
         * Set bearer token in local storage
         * After successful login
         * @param {*} sessionToken 
         */
        setBearerToken(sessionToken) {
            LocalStorage.set(process.env.SESSION_NAME, sessionToken)
        },

        /**
         * Check current session
         * @returns boolean
         */
        checkBearerTokenSet() {
            return LocalStorage.getItem(process.env.SESSION_NAME) ? true : false
        },

        /**
         * remove bearer token from local storage
         */
        removeBearerToken() {
            LocalStorage.remove(process.env.SESSION_NAME)
        },

        /**
         * Load bearer token from local storage
         * Set new user session
         */
        setSession() {
            const token = LocalStorage.getItem(process.env.SESSION_NAME);
            if(token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
                this.access.bearer_token = true;
            };
        },
        
        /**
         * Remoce session
         */
        removeSession() {
            axios.defaults.headers.common['Authorization'] = '';
            this.access.bearer_token = false
            this.access.user = false;
            this.access.admin = false;
            this.access.tokens = [];
            this.user = {};
        },
    }
});

export default storeUser;
