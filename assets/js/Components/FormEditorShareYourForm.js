'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import CustomObjectForm from './CustomObjectForm';
import CreateFolderForm from "./CreateFolderForm";

class FormEditorShareYourForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param form
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, form) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.form = form;

        this.$wrapper.on('click', FormEditorShareYourForm._selectors.shareButton, this.copyText.bind(this));

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            shareButton: '.js-share-button',
            shareableLink: '.js-shareable-link'
        }
    }

    render() {
        this.$wrapper.html(FormEditorShareYourForm.markup(this));
    }

    copyText() {
        /* Get the text field */
        let copyText = $(FormEditorShareYourForm._selectors.shareableLink).get(0);

        /* Select the text field */
        copyText.select();

        /* Copy the text inside the text field */
        document.execCommand("copy");
    }

    static markup({form}) {
        return `
        <div class="container">
            
            <div class="d-table-cell w-100">
                <input class="form-control no-right-radius js-shareable-link" value="${Routing.generate('form', {uid: form.uid}, true)}">
            </div>
             <div class="d-table-cell align-middle">
                <button class="btn btn-primary no-left-radius js-share-button">Share</button>
            </div>
        </div>
    `;
    }
}

export default FormEditorShareYourForm;