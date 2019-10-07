'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';

class DeleteWorkflowForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param uid
     */
    constructor($wrapper, globalEventDispatcher, portal, uid) {

        debugger;
        this.$wrapper = $wrapper;
        this.portal = portal;
        this.uid = uid;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            DeleteWorkflowForm._selectors.deleteForm,
            this.handleDeleteFormSubmit.bind(this)
        );

        this.loadDeleteForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            deleteForm: '.js-delete-workflow-form',
        }
    }

    loadDeleteForm() {
        debugger;
        $.ajax({
            url: Routing.generate('delete_workflow', {internalIdentifier: this.portal, uid: this.uid}),
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

        this._delete(formData)
            .then((data) => {

                debugger;
                swal("Hooray!", "Sweet! Workflow successfully removed!", "success");
                this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_DELETED);
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
    _delete(data) {
        return new Promise( (resolve, reject) => {

            const url = Routing.generate('delete_workflow', {internalIdentifier: this.portal, uid: this.uid});
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

export default DeleteWorkflowForm;