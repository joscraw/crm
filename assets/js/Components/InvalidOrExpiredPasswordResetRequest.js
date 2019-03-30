'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import ResetPasswordForm from "./ResetPasswordForm";

class InvalidOrExpiredPasswordResetRequest {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {

        this.$wrapper.html(InvalidOrExpiredPasswordResetRequest.markup());

    }

    static markup() {

        return `
        <h3 class="p-login__heading">Reset Password Error</h3>
        <br>
        <p class="p-login__heading">Invalid or expired password reset request</p>
        <div class="row">
            <div class="col-md-12" align="center">
                <a href="${Routing.generate('app_login')}" class="p-login__login-button" style="display: inline-block; margin-top: 5%">Back to login</a>
            </div>
        </div>
    `;
    }
}

export default InvalidOrExpiredPasswordResetRequest;