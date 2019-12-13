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

       /* this.unbindEvents();

        this.$wrapper.on(
            'click',
            SingleLineTextFieldFilterForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            SingleLineTextFieldFilterForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            SingleLineTextFieldFilterForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.setupFormAttributes();*/

        this.render();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            backToListButton: '.js-back-to-list-button',
            applyFilterForm: '#js-apply-filter-form',
            radioButton: '.js-radio-button',
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', '#js-apply-filter-form');
        this.$wrapper.off('click', '.js-radio-button');
        this.$wrapper.off('click', SingleLineTextFieldFilterForm._selectors.backToListButton);
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

    setupFormAttributes() {

        this.operator1 = StringHelper.makeCharId();
        this.operator2 = StringHelper.makeCharId();
        this.operator3 = StringHelper.makeCharId();
        this.operator4 = StringHelper.makeCharId();

    }

    handleNewFilterFormSubmit(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        const customFilter = {...this.property, ...formData};

        customFilter.property = this.property;

        this.globalEventDispatcher.publish(Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED, customFilter);
        console.log(`Event Dispatched: ${Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED}`);

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
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">
          <div class="form-group js-operator-value">
            <input type="text" value="${property.custom_object_label} ${property.label}" name="value" class="form-control" autocomplete="off">
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