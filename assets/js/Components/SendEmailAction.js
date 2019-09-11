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
        }
    }

    bindEvents() {

        this.$wrapper.on(
            'submit',
            SendEmailAction._selectors.form,
            this.handleSendEmailActionFormSubmit.bind(this)
        );

        return this;
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', SendEmailAction._selectors.form);

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
        return this;
    }

    static markup({customObject: {label}}) {

        return `
        <div>
            <button type="button" class="btn btn-link js-backButton float-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
        </div>
        <form name="sendEmailActionForm" id="js-send-email-action-form" novalidate="novalidate">
            <div class="form-group">
                <label for="toAddresses">To</label>
                <input type="text" name="toAddresses" class="form-control" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" name="subject" class="form-control" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control" id="body" name="body" rows="5"></textarea>
            </div>
            <button type="submit" class="js-apply-action-button btn btn-light btn--full-width">Apply action</button>
        </form>
        `;
    }
}

export default SendEmailAction;