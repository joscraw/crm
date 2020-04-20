// assets/js/utils/Auth.js

import auth0 from 'auth0-js';

const AUTH0_CLIENT_ID = process.env.AUTH0_CLIENT_ID,
      AUTH0_REDIRECT_URI = process.env.AUTH0_REDIRECT_URI,
      AUTH0_DOMAIN = process.env.AUTH0_DOMAIN,
      AUTH0_CONNECTION = process.env.AUTH0_CONNECTION,
      AUTH0_AUDIENCE = process.env.AUTH0_AUDIENCE,
      AUTH0_RETURN_TO = process.env.AUTH0_RETURN_TO;

class Auth {
    constructor() {

        this.auth0 = new auth0.WebAuth({
            domain: AUTH0_DOMAIN,
            audience: AUTH0_AUDIENCE,
            clientID: AUTH0_CLIENT_ID,
            redirectUri: AUTH0_REDIRECT_URI,
            responseType: 'token id_token',
            scope: 'openid profile'
        });

        this.getProfile = this.getProfile.bind(this);
        this.handleAuthentication = this.handleAuthentication.bind(this);
        this.isAuthenticated = this.isAuthenticated.bind(this);
        this.logIn = this.logIn.bind(this);
        this.logOut = this.logOut.bind(this);
    }

    static get AUTH0_CLIENT_ID() {
        return AUTH0_CLIENT_ID;
    }

    static get AUTH0_REDIRECT_URI() {
        return AUTH0_REDIRECT_URI;
    }

    static get AUTH0_DOMAIN() {
        return AUTH0_DOMAIN;
    }

    static get AUTH0_CONNECTION() {
        return AUTH0_CONNECTION;
    }

    static get AUTH0_AUDIENCE() {
        return AUTH0_AUDIENCE;
    }

    static get AUTH0_RETURN_TO() {
        return AUTH0_RETURN_TO;
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
        this.profile = authResult.idTokenPayload;
        this.accessToken = authResult.accessToken;
        this.expiresAt = authResult.expiresIn * 1000 + new Date().getTime();
    }

    isAuthenticated() {
        return new Date().getTime() < this.expiresAt;
    }

    logIn() {
        debugger;
        this.auth0.authorize({
            connection: AUTH0_CONNECTION
        });
    }

    logOut() {

        this.auth0.logout({
            returnTo: AUTH0_RETURN_TO,
            clientID: AUTH0_CLIENT_ID,
            federated: true,
        });
    }

    silentAuth() {
        return new Promise((resolve, reject) => {
            this.auth0.checkSession({connection: AUTH0_CONNECTION}, (err, authResult) => {
                if (err) return reject(err);
                this.setSession(authResult);
                resolve();
            });
        });
    }
}

const auth0Client = new Auth();

export default auth0Client;
// Export a non instantiated version of the class as well
export {
    Auth
}