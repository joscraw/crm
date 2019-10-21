'use strict';

import Settings from '../Settings';
import Routing from "../Routing";
import $ from "jquery";
import PropertySearch from "./PropertySearch";
import List from "list.js";
import SingleLineTextFieldFilterForm from "./SingleLineTextFieldFilterForm";
import ColumnSearch from "./ColumnSearch";
import ContextHelper from "../ContextHelper";
import ListFilterList from "./ListFilterList";

class SendEmailAction {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, uid, customObject, join = null, joins = [], referencedFilterPath = []) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.lists = [];
        this.uid = uid;
        this.customObject = customObject;
        this.join = join;
        this.joins = joins;
        this.lists = [];
        this.referencedFilterPath = referencedFilterPath;

        this.unbindEvents()
            .bindEvents()
            .render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            form: '#js-send-email-action-form',
            mergeTags: '.js-possible-merge-tags',
            mergeTag: '.js-merge-tag',
            toAddresses: '#toAddresses',
            subject: '#subject',
            body: '#body'
        }
    }

    bindEvents() {
        this.$wrapper.on('submit', SendEmailAction._selectors.form, this.handleSendEmailActionFormSubmit.bind(this));
        this.$wrapper.on('click', SendEmailAction._selectors.mergeTag, this.insertText.bind(this));
        this.$wrapper.on('focus', SendEmailAction._selectors.toAddresses, this.setActiveFormField.bind(this));
        this.$wrapper.on('focus', SendEmailAction._selectors.subject, this.setActiveFormField.bind(this));
        this.$wrapper.on('focus', SendEmailAction._selectors.body, this.setActiveFormField.bind(this));
        return this;
    }

    setActiveFormField(e) {
        this.activeFormFieldName = $(e.target).attr('name');
    }

    insertText(e) {
        let copyText = $(e.target).text();

        if(this.activeFormFieldName === 'toAddresses') {
            this.insertAtCursor(this.$wrapper.find('#toAddresses').get(0), copyText);
        } else if(this.activeFormFieldName === 'subject') {
            this.insertAtCursor(this.$wrapper.find('#subject').get(0), copyText);
        } else if(this.activeFormFieldName === 'body') {
            this.insertAtCursor(this.$wrapper.find('#body').get(0), copyText);
        }
    }

    insertAtCursor (input, textToInsert) {
        // get current text of the input
        const value = input.value;

        // save selection start and end position
        const start = input.selectionStart;
        const end = input.selectionEnd;

        // update the value with our text inserted
        input.value = value.slice(0, start) + textToInsert + value.slice(end);

        // update cursor to be at the end of insertion
        input.selectionStart = input.selectionEnd = start + textToInsert.length;
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', SendEmailAction._selectors.form);
        this.$wrapper.off('click', SendEmailAction._selectors.mergeTag);
        this.$wrapper.off('focus', SendEmailAction._selectors.toAddresses);
        this.$wrapper.off('focus', SendEmailAction._selectors.subject);
        this.$wrapper.off('focus', SendEmailAction._selectors.body);

        return this;
    }

    handleBackButtonClicked(e) {

        debugger;
        e.stopPropagation();

        this.globalEventDispatcher.publish(
            Settings.Events.WORKFLOW_BACK_BUTTON_CLICKED,
            Settings.VIEWS.WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE
        );
    }

    handleSendEmailActionFormSubmit(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        this.globalEventDispatcher.publish(Settings.Events.WORKFLOW_SEND_EMAIL_ACTION_FORM_SUBMIT, formData);
        console.log(`Event Dispatched: ${Settings.Events.WORKFLOW_SEND_EMAIL_ACTION_FORM_SUBMIT}`);
    }

    render() {
        this.$wrapper.html(SendEmailAction.markup(this));
        this.$wrapper.find('#toAddresses').focus();
        $('[data-toggle="tooltip"]').tooltip();
        this.loadMergeTags().then(data => {
            this.mergeTags = data.data;
            this.renderMergeTags(this.mergeTags);
        });
        return this;
    }

    loadMergeTags() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'get_merge_tags', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObject.internalName});

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    renderMergeTags(mergeTags) {

        for(let mergeTag of mergeTags) {
            this.$wrapper.find(SendEmailAction._selectors.mergeTags).append(
                ' <a href="javascript:void(0)" class="js-merge-tag">'+mergeTag+'</a> '
            );
        }

        debugger;
    }

    static markup({customObject: {label}}) {

        return `
        <div>
            <button type="button" class="btn btn-link js-backButton float-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
        </div>
        <form name="sendEmailActionForm" id="js-send-email-action-form" novalidate="novalidate">
            <div class="form-group">
                <label for="toAddresses">To</label>
                <input type="text" name="toAddresses" id="toAddresses" class="form-control" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" name="subject" class="form-control" id="subject" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control" id="body" name="body" rows="5"></textarea>
            </div>
            <button type="submit" class="js-apply-action-button btn btn-light btn--full-width">Apply action</button>
        </form>
        <hr>
        <h2>Possible Merge Tags <small><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="Merge tags can be used to populate the form/email with dynamic data from your objects. Click anywhere inside the form, and then select a merge tag to insert into the form."></i></small></h2>
        <div class="js-possible-merge-tags"></div>
        `;
    }
}

export default SendEmailAction;