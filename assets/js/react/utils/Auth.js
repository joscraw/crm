// assets/js/utils/Auth.js

import auth0 from 'auth0-js';

class Auth {
    constructor() {
        // todo should be pulling this from env file somehow?
        this.auth0 = new auth0.WebAuth({
            domain: 'crm-development.auth0.com',
            audience: 'https://crm.dev/api',
            clientID: 'Hhzjj4oe1CuKYcd9C0nbSjh5ltScR5oL',
            redirectUri: 'https://crm.dev/callback',
            responseType: 'token id_token',
            scope: 'openid profile'
        });

        this.getProfile = this.getProfile.bind(this);
        this.handleAuthentication = this.handleAuthentication.bind(this);
        this.isAuthenticated = this.isAuthenticated.bind(this);
        this.logIn = this.logIn.bind(this);
        this.logOut = this.logOut.bind(this);
    }

    getProfile() {
        return this.profile;
    }

    getAccessToken() {
        return this.accessToken;
    }

    handleAuthentication() {
        return new Promise((resolve, reject) => {
            this.auth0.parseHash((err, authResult) => {
                debugger;
                if (err) return reject(err);
                if (!authResult || !authResult.idToken) {
                    return reject(err);
                }
                this.setSession(authResult);
                resolve();
            });
        })
    }

    setSession(authResult, step) {
        debugger;
        this.profile = authResult.idTokenPayload;
        this.accessToken = authResult.accessToken;
        this.expiresAt = authResult.expiresIn * 1000 + new Date().getTime();
    }

    isAuthenticated() {
        return new Date().getTime() < this.expiresAt;
    }

    logIn() {
        debugger;
        this.auth0.authorize();
    }

    // todo make sure this is federated
    logOut() {

        this.auth0.logout({
            returnTo: 'https://crm.dev/logout',
            clientID: 'Hhzjj4oe1CuKYcd9C0nbSjh5ltScR5oL',
        });
    }

    silentAuth() {
        debugger;
        return new Promise((resolve, reject) => {
            debugger;
            this.auth0.checkSession({}, (err, authResult) => {
                debugger;
                if (err) return reject(err);
                this.setSession(authResult);
                resolve();
            });
        });
    }
}

const auth0Client = new Auth();

export default auth0Client;