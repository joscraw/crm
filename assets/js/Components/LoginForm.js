'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class LoginForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.unbindEvents();

        this.$wrapper.on(
            'submit',
            LoginForm._selectors.loginForm,
            this.handleLoginFormSubmit.bind(this)
        );

        this.loadLoginForm();

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            loginForm: '.js-login-form',
        }
    }

    unbindEvents() {

        this.$wrapper.off('submit', LoginForm._selectors.loginForm);

    }

    loadLoginForm() {
        $.ajax({
            url: Routing.generate('login_form'),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleLoginFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._login(formData)
            .then((data) => {

                if(data.success === false) {

                    toastr.options.showMethod = "slideDown";
                    toastr.options.hideMethod = "slideUp";
                    toastr.options.preventDuplicates = true;

                    toastr.error(data.message, {positionClass : "toast-top-center"});

                } else {
                    window.location = data.targetPath;
                }
            }).catch((errorData) => {});
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _login(data) {
        return new Promise( (resolve, reject) => {
            debugger;
            const url = Routing.generate('app_login');

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
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default LoginForm;