'use strict';
import { Loading, QSpinnerGears, Notify } from 'quasar';
import store from "src/stores/user.js";

/* ***********************************************
 * Request Handling
 *  > Constructor
 *      > Default Attributes
 *      > Custom ErrorHandling
 *  > Loading, waiting for response
 *  > Success, response is successful
 *  > Error Handling, there was an error
 ************************************************ */
export class ResponseHandler {

    /**
     ** Hanlding Server Response
     **     > Default Attributes
     **     > ErrorHandling 
     * @param {Object} router 
     * @param {Object} app 
     */
    constructor(router, app) {
        this.loading = false;
        this.app = app;
        this.message = '';
        this.router = router;
        this.progressBar = null;
        this.notify = null;
        this.position = 'bottom-right';
        this.durationSuccess = 1900;
        this.durationError = 5500;
        this.class = "toaster-container"
        this.defaultLoadMessage = "Processing data. Please wait..."
        this.defaultSuccessMessage = "Your settings have been saved.";
        this.defaultErrorMessage = "Ops, some error occured.";
    }

    /**
     ** Error Server Responses are managed here
     ** According to Server Middleware-Definitions
     *
     * @param {Object} serverResponse 
     * @param {Object} router 
     */
     errorHandling(serverResponse, router) {
        
        // Email not verified
        if(serverResponse.status === 401 && serverResponse.data.status === 'email_not_verified') {
            store().removeBearerToken();
            store().removeSession();
            router.push({
                name: 'EmailVerificationRequest', 
                params: { 
                    email: serverResponse.data.email,
                }
            });
            throw serverResponse.data.message;
        }

        // No Admin
        else if (serverResponse.status === 401 && serverResponse.data.status === 'no_admin') {
            store().removeAdmin();
            router.push('/');
            throw serverResponse.data.message;
        }

        // No access to Service / Subscription
        else if(serverResponse.status === 401 && serverResponse.data.status === 'no_access_to_service') {
            store().removeAccess(serverResponse.data.access_token);
            router.push('/services');
            throw serverResponse.data.message 
                ? serverResponse.data.message 
                : 'Your subscription is expired.'
        }

        // Ongoing subscriptions
        else if(serverResponse.status === 422 && serverResponse.data.status === 'active_subscriptions') {
            router.push('/account/access');
            throw serverResponse.data.message 
                ? serverResponse.data.message 
                : 'Please cancel active subscriptions.'
        }

        // No access
        else if(serverResponse.status === 401) {
            store().removeBearerToken();
            store().removeSession();
            router.push('/');
            throw serverResponse.data.message 
                ? serverResponse.data.message 
                : 'Hmm, some error occured. Please try again.'
        }
    }

    showNotify(message, type, duration) {
        this.notify = Notify.create({
            position: this.position,
            type: type,
            message: message,
            timeout: duration,
            classes: this.class
        });
    }

    load(message = this.defaultLoadMessage) {
        this.loading = true;
        Loading.show({
            boxClass: 'page-loading-block',
            spinner: QSpinnerGears,
            message: message,
        })
        return true;
    }

    done() {
        this.loading = false;
        Loading.hide();
        return false;
    }

    success(successMessage = this.defaultSuccessMessage) {
        this.loading = false;
        this.done();
        if(this.notify) this.notify();
        this.showNotify(successMessage, 'positive', this.durationSuccess)
        return successMessage;
    }

    /**
     * Show Error by Notification
     *  > String (as Customerror) vs Object (as Serverresponse)
     *  > Handle Response Error Status
     *
     * @param {*} responseError String | Object
     * @return { String } 
     */
    error(responseError) {
        try {
            this.loading = false;
            this.done();

            // String
            if (typeof responseError === 'string') 
                this.message = responseError
            // Error response by processing request
            else if (typeof responseError === 'object') {
                // Error response by server
                if(responseError.data) {
                    this.errorHandling(responseError, this.router);
                    this.message = responseError.data.message 
                        ?  responseError.data.message
                        :  responseError.status
                            ? responseError.status
                            : this.defaultErrorMessage;
                } 
                // Error response by client
                else if (responseError.message) {
                    this.message = responseError.message;
                }
            }
        } catch (error) {
            // Only Client or Custom Message
            this.message = error.message ? error.message : error;
        } finally {                                                 // Show Notification
            try {
                if(this.notify) this.notify();
                this.showNotify(this.message, 'negative', this.durationError)
            } catch (error) {
                console.log('notification.error', error)
            }
        }
        
        console.log('Error Response:', this.message)
        return this.message;
    }
}
