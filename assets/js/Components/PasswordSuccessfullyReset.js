'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import ResetPasswordForm from "./ResetPasswordForm";

class PasswordSuccessfullyReset {

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

        this.$wrapper.html(PasswordSuccessfullyReset.markup());

        toastr.options.showMethod = "slideDown";
        toastr.options.hideMethod = "slideUp";
        toastr.options.preventDuplicates = true;

        toastr.success('Password Successfully Reset', {positionClass : "toast-top-center"});

    }

    static markup() {

        return `
        <h3 class="p-login__heading">Password Successfully Reset</h3>
        <br> 
        <div class="row">
            <div class="col-md-12" align="center">
                <a href="${Routing.generate('app_login')}" class="p-login__login-button" style="display: inline-block; margin-top: 5%">Back to login</a>
            </div>
        </div>
    `;
    }
}

export default PasswordSuccessfullyReset;