'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import CreateFolderForm from "./CreateFolderForm";
import FormEditorShareYourForm from "./FormEditorShareYourForm";

class FormEditorShareYourFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param form
     */
    constructor(globalEventDispatcher, portalInternalIdentifier, form) {
        debugger;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.form = form;

        this.render();
    }

    render() {
        swal({
            title: 'Share your form',
            showConfirmButton: false,
            html: FormEditorShareYourFormModal.markup(this)
        });

        new FormEditorShareYourForm($('.js-form-editor-share-your-form-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.form);
    }

    static markup() {
        return `
        <div class="js-form-editor-share-your-form-container"></div>
    `;
    }
}

export default FormEditorShareYourFormModal;