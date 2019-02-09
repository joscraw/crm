'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

require('jquery-ui-dist/jquery-ui');
require('jquery-ui-dist/jquery-ui.css');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker.css');

class DatePickerFieldFilterForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, property) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.property = property;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            DatePickerFieldFilterForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            DatePickerFieldFilterForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            DatePickerFieldFilterForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.render();
        this.activatePlugins();
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

    activatePlugins() {
        $('.js-datepicker').datepicker({
            format: 'mm-dd-yyyy'
        });
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', '#js-apply-filter-form');
        this.$wrapper.off('click', '.js-radio-button');
        this.$wrapper.off('click', DatePickerFieldFilterForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        this.$wrapper.html(DatePickerFieldFilterForm.markup(this));
        this.$wrapper.find('.js-radio-button').first().click();
    }

    handleNewFilterFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        const formData = {};

        for (let fieldData of $form.serializeArray()) {
            formData[fieldData.name] = fieldData.value
        }

        const customFilter = {...this.property, ...formData};

        this.globalEventDispatcher.publish(Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED, customFilter);
        console.log(`Event Dispatched: ${Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED}`);

    }

    handleOperatorRadioButtonClicked(e) {

        debugger;
        this.$wrapper.find('.js-operator-value').remove();

        let $radioButton = $(e.currentTarget);
        if($radioButton.attr('data-has-text-input')) {
            const html = textFieldTemplate();
            const $textField = $($.parseHTML(html));
            $radioButton.closest('div').after($textField);
        } else if($radioButton.attr('data-has-number-in-between-input')) {
            const html = dateInBetweenTemplate();
            const $textField = $($.parseHTML(html));
            $radioButton.closest('div').after($textField);
        }

        this.activatePlugins();
    }

    static markup({property}) {

        return `
        <button type="button" class="btn btn-link js-back-to-list-button"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${property.label}*</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">
            <input type="hidden" name="property" value="${property.internalName}">
            <input type="hidden" name="fieldType" value="${property.fieldType}">
            <input type="hidden" name="label" value="${property.label}">
            <input type="hidden" name="id" value="${property.id}">
            <div style="height: 200px; overflow-y: auto">
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator1" value="EQ" checked data-has-text-input="true">
                    <label class="form-check-label" for="operator1">
                     <p>is equal to</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator2" value="NEQ" data-has-text-input="true">
                    <label class="form-check-label" for="operator2">
                    <p>is not equal to</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator3" value="LT" checked data-has-text-input="true">
                    <label class="form-check-label" for="operator3">
                     <p>is before</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator4" value="GT" data-has-text-input="true">
                    <label class="form-check-label" for="operator4">
                    <p>is after</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator5" value="BETWEEN" data-has-number-in-between-input="true">
                    <label class="form-check-label" for="operator5">
                    <p>is between</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator6" value="HAS_PROPERTY">
                    <label class="form-check-label" for="operator6">
                    <p>is known</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator7" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="operator7">
                    <p>is unknown</p>
                    </label>
                </div>
            </div>
            <button type="submit" class="js-apply-filter-button btn btn-light btn--full-width">Apply filter</button>
        </form>
    `;
    }
}


const textFieldTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="value" class="form-control js-datepicker" autocomplete="off">
  </div>
    
`;

const dateInBetweenTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="low_value" class="form-control js-datepicker" autocomplete="off">
  </div>
  <span>and</span>
  <div class="form-group js-operator-value">
    <input type="text" name="high_value" class="form-control js-datepicker" autocomplete="off">
  </div>
`;

export default DatePickerFieldFilterForm;