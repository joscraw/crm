'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class ResetPasswordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param token
     */
    constructor($wrapper, globalEventDispatcher, token) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.token = token;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            ResetPasswordForm._selectors.resetPasswordForm,
            this.handleResetPasswordFormSubmit.bind(this)
        );

        Pace.start({
            target: '.p-login__right'
        });

        this.loadResetPasswordForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            resetPasswordForm: '.js-reset-password-form',
        }
    }

    unbindEvents() {

        this.$wrapper.off('submit', ResetPasswordForm._selectors.resetPasswordForm);

    }

    loadResetPasswordForm() {
        debugger;
        $.ajax({
            url: Routing.generate('reset_password_form', {token: this.token}),
        }).then(data => {
            debugger;
            this.$wrapper.html(data.formMarkup);
        }).catch((jqXHR) => {

            const errorData = JSON.parse(jqXHR.responseText);

            debugger;
            if(errorData.success === false && _.has(errorData, 'message')) {

                debugger;
                toastr.options.showMethod = "slideDown";
                toastr.options.hideMethod = "slideUp";
                toastr.options.preventDuplicates = true;

                toastr.error(errorData.message, {positionClass : "toast-top-center"});

                this.globalEventDispatcher.publish(Settings.Events.INVALID_OR_EXPIRED_PASSWORD_RESET_REQUEST);
            }

        });
    }

    /**
     * @param e
     */
    handleResetPasswordFormSubmit(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._resetPassword(formData)
            .then((data) => {

                this.globalEventDispatcher.publish(Settings.Events.PASSWORD_SUCCESSFULLY_RESET);

            }).catch((errorData) => {

                debugger;

            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _resetPassword(data) {
        debugger;
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('reset_password_form', {token: this.token});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                debugger;
                resolve(data);
            }).catch((jqXHR) => {
                debugger;
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default ResetPasswordForm;