'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";
import StringHelper from "../StringHelper";

class ListEditColumnNameForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, property) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.property = property;
        this.unbindEvents();
        this.$wrapper.on(
            'submit',
            ListEditColumnNameForm._selectors.editColumnNameForm,
            this.handleFormSubmit.bind(this)
        );
        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToListButton: '.js-back-to-list-button',
            editColumnNameForm: '#js-edit-column-name-form',
            radioButton: '.js-radio-button',
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', ListEditColumnNameForm._selectors.editColumnNameForm);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        this.$wrapper.html(ListEditColumnNameForm.markup(this));
       /* this.$wrapper.find('.js-radio-button').first().click();
        if(this.hideBackButton) {
            this.$wrapper.find('.js-back-to-list-button').remove();
        }*/
    }

    handleFormSubmit(e) {
        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }
        const $form = $(e.currentTarget);
        const formData = {};
        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }
        const property = {...this.property, ...formData};
        debugger;
        this.globalEventDispatcher.publish(Settings.Events.LIST_COLUMN_NAME_CHANGED, property);
    }

    handleOperatorRadioButtonClicked(e) {
        this.$wrapper.find('.js-operator-value').remove();
        let $radioButton = $(e.currentTarget);
        if($radioButton.attr('data-has-text-input')) {
            const html = textFieldTemplate();
            const $textField = $($.parseHTML(html));
            $radioButton.closest('div').after($textField);
        }
        this.$wrapper.find('.js-operator-value').find('input[type="text"]').focus();
    }

    static markup({property}) {

        return `
        <form name="filter" id="js-edit-column-name-form" novalidate="novalidate">
          <div class="form-group js-operator-value">
            <input type="text" value="${property.column_label}" name="column_label" class="form-control" autocomplete="off">
          </div>
            <button type="submit" class="js-apply-filter-button btn btn-light btn--full-width">Change Name</button>
        </form>
    `;
    }
}


const textFieldTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="value" class="form-control" autocomplete="off">
  </div>
    
`;

export default ListEditColumnNameForm;