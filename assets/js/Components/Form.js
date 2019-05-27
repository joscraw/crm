'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import swal from "sweetalert2";
require('jquery-ui-dist/jquery-ui');
import * as Cookies from "js-cookie";

class Form {

    constructor($wrapper, globalEventDispatcher, uid) {
        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.uid = uid;
        this.form = null;

        this.$wrapper.on(
            'submit',
            Form._selectors.form,
            this.handleFormSubmit.bind(this)
        );

        this.loadFormObj().then((data) => {
            debugger;
            this.form = data.data;
            this.render();
        });

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            formContainer: '.js-form-container',
            cardContainer: '.js-card-container',
            form: '.js-form',
            messageContainer: '.js-message-container'
        }
    }

    render() {

        this.$wrapper.html(Form.markup(this));

        this.loadForm().then((data) => {
            this.$wrapper.find(Form._selectors.formContainer).html(data.formMarkup);
            this.activatePlugins();
        });
    }

    loadForm() {

        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('get_form', {uid: this.uid}),
                method: 'GET'
            }).then(data => {
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    loadFormObj() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_form_data', {uid: this.uid});
            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    activatePlugins() {

        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        debugger;

        const url = Routing.generate('records_for_selectize', {internalIdentifier: this.form.portal.internalIdentifier, internalName: this.form.customObject.internalName});

        $('.js-selectize-single-select-with-search').each((index, element) => {

            let select = $(element).selectize({
                valueField: 'valueField',
                labelField: 'labelField',
                searchField: 'searchField',
                load: (query, callback) => {

                    if (!query.length) return callback();
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            search: query,
                            allowed_custom_object_to_search: $(element).data('allowedCustomObjectToSearch'),
                            property_id: $(element).data('propertyId')
                        },
                        error: () => {
                            callback();
                        },
                        success: (res) => {
                            select.selectize()[0].selectize.clearOptions();
                            select.options = res;
                            callback(res);
                        }
                    })
                }
            });
        });
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        if(this.isCookieTrackingEnabled() && this.cookieExistsForForm()) {
            this.toggleMessageContainer();
            this.$wrapper.find(Form._selectors.messageContainer).html(formAlreadySubmittedTemplate());
            return;
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._submit(formData)
            .then((data) => {
                this.submitAction();
                this.setCookie();
            }).catch((errorData) => {
            this.$wrapper.find(Form._selectors.formContainer).html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    isCookieTrackingEnabled() {
        return this.form.cookieTracking;
    }

    submitAction() {

        switch(this.form.submitAction) {
            case 'REDIRECT':
                debugger;
                window.location = this.form.redirectUrl;
                break;
            case 'MESSAGE':
                this.toggleMessageContainer();
                this.$wrapper.find(Form._selectors.messageContainer).html(submitMessageTemplate(this.form.submitMessage));
                break;
        }
    }

    setCookie() {
        let forms = [];
        if(Cookies.getJSON('forms')) {
            forms = Cookies.getJSON('forms');
        }
        forms.push(this.form.uid);
        Cookies.set('forms', forms);
    }

    cookieExistsForForm() {
        let forms = Cookies.getJSON('forms');
        return (forms && forms.includes(this.form.uid));
    }

    toggleMessageContainer() {
        if(this.$wrapper.find(Form._selectors.messageContainer).hasClass('d-none')) {
            this.$wrapper.find(Form._selectors.messageContainer).removeClass('d-none');
        }

        if(!this.$wrapper.find(Form._selectors.cardContainer).hasClass('d-none')) {
            this.$wrapper.find(Form._selectors.cardContainer).addClass('d-none');
        }
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _submit(data) {

        debugger;
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('form_submit', {uid: this.uid});

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

    static markup() {

        debugger;

        return `
          <div class="card js-card-container">
              <div class="card-body">
                <div class="js-form-container"></div>
              </div>
          </div>
          <div class="js-message-container d-none"></div>
    `;
    }
}

/**
 * @return {string}
 */
const submitMessageTemplate = (submitMessage) => `
    <h1 style="text-align: center; margin-top: 150px">${submitMessage}</h1>
`;

/**
 * @return {string}
 */
const formAlreadySubmittedTemplate = () => `
    <h1 style="text-align: center; margin-top: 150px">Woahhh!!! Slow down there. You already submitted this form!</h1>
`;

export default Form;