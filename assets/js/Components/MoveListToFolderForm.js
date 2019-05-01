'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class MoveListToFolderForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param listId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, listId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.listId = listId;

        this.$wrapper.on(
            'submit',
            MoveListToFolderForm._selectors.form,
            this.handleFormSubmit.bind(this)
        );

        this.loadForm().then(() => { this.activatePlugins(); });

        this.activatePlugins();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            form: '.js-move-list-to-folder-form'
        }
    }

    activatePlugins() {

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });
    }

    loadForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: Routing.generate('move_list_to_folder', {internalIdentifier: this.portalInternalIdentifier, listId: this.listId}),
            }).then(data => {
                this.$wrapper.html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._moveList(formData)
            .then((data) => {
                swal("Hooray!", "Hooray!, list successfully moved!", "success");
                this.globalEventDispatcher.publish(Settings.Events.LIST_MOVED_TO_FOLDER);
            }).catch((errorData) => {

            if(errorData.httpCode === 401) {
                swal("Woah!", `You don't have proper permissions for this!`, "error");
                return;
            }

            this.$wrapper.html(errorData.formMarkup);
            this.activatePlugins();
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _moveList(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('move_list_to_folder', {internalIdentifier: this.portalInternalIdentifier, listId: this.listId});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default MoveListToFolderForm;