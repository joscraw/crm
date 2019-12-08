'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteRecordForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, portal, recordId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.recordId = recordId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteRecordForm._selectors.deleteRecordForm,
            this.handleDeleteFormSubmit.bind(this)
        );
        this.loadDeleteRecordForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteRecordForm: '.js-delete-record-form',
        }
    }

    loadDeleteRecordForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_record_form', {internalIdentifier: this.portal, recordId: this.recordId}),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleDeleteFormSubmit(e) {
        if(e.cancelable) {
            e.preventDefault();
        }
        debugger;
        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));
        this._deleteRecord(formData)
            .then((data) => {
                swal("Hooray!", "Sweet! Record successfully removed!", "success");
                this.globalEventDispatcher.publish(Settings.Events.RECORD_DELETED);
            }).catch((errorData) => {

                if(errorData.httpCode === 401) {
                    swal("Woah!", `You don't have proper permissions for this!`, "error");
                    return;
                }
                this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _deleteRecord(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('delete_record', {internalIdentifier: this.portal, recordId: this.recordId});
            debugger;

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

export default DeleteRecordForm;