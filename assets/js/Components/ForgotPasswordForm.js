'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class ForgotPasswordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        debugger;
        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            ForgotPasswordForm._selectors.forgotPasswordForm,
            this.handleForgotPasswordFormSubmit.bind(this)
        );

        this.loadForgotPasswordForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            forgotPasswordForm: '.js-forgot-password-form',
        }
    }

    unbindEvents() {

        this.$wrapper.off('submit', ForgotPasswordForm._selectors.forgotPasswordForm);

    }

    loadForgotPasswordForm() {
        debugger;
        $.ajax({
            url: Routing.generate('forgot_password_form'),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleForgotPasswordFormSubmit(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._forgotPassword(formData)
            .then((data) => {

                if(data.success === true && _.has(data, 'message')) {

                    toastr.options.showMethod = "slideDown";
                    toastr.options.hideMethod = "slideUp";
                    toastr.options.preventDuplicates = true;

                    toastr.success(data.message, {positionClass : "toast-top-center"});

                }

            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _forgotPassword(data) {
        debugger;
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('forgot_password_form');

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

export default ForgotPasswordForm;