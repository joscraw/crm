'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteReportForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param reportId
     */
    constructor($wrapper, globalEventDispatcher, portal, reportId) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.reportId = reportId;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteReportForm._selectors.deleteReportForm,
            this.handleDeleteFormSubmit.bind(this)
        );
        this.loadDeleteReportForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteReportForm: '.js-delete-report-form',
        }
    }

    loadDeleteReportForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_report_form', {internalIdentifier: this.portal, reportId: this.reportId}),
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

        this._deleteReport(formData)
            .then((data) => {

                debugger;
                swal("Hooray!", "Sweet! Report successfully removed!", "success");
                this.globalEventDispatcher.publish(Settings.Events.REPORT_DELETED);
            }).catch((errorData) => {

                if(errorData.httpCode === 401) {
                    swal("Woah!", `You don't have proper permissions for this!`, "error");
                    return;
                }

                this.$wrapper.html(errorData.formMarkup);

            // Use for when the form is being generated on the JS side
            /*this._mapErrorsToForm(errorData.errors);*/
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _deleteReport(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_report', {internalIdentifier: this.portal, reportId: this.reportId});
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

export default DeleteReportForm;