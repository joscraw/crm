'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class EditNumberFieldFilterForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, customFilter) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.customFilter = customFilter;

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            EditNumberFieldFilterForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            EditNumberFieldFilterForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            EditNumberFieldFilterForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

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
        this.$wrapper.off('click', EditNumberFieldFilterForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        this.$wrapper.html(EditNumberFieldFilterForm.markup(this));
        this.setRadioOption();
    }

    setRadioOption() {
        debugger;
        this.$wrapper.find('.js-radio-button').each((index, element) => {
            let value = $(element).val();
            let $radioButton = $(element);
            if(this.customFilter.operator === value) {
                $(element).click();
                if($radioButton.attr('data-has-text-input')) {
                    this.$wrapper.find('.js-operator-value input[type="text"]').val(this.customFilter.value);
                } else if($radioButton.attr('data-has-number-in-between-input')) {
                    this.$wrapper.find('.js-operator-value input[name="low_value"]').val(this.customFilter.low_value);
                    this.$wrapper.find('.js-operator-value input[name="high_value"]').val(this.customFilter.high_value);
                }
            }
        });
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

        const customFilter = {...this.customFilter, ...formData};

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
        } else if($radioButton.attr('data-has-number-in-between-input')) {
            const html = numberInBetweenTemplate();
            const $textField = $($.parseHTML(html));
            $radioButton.closest('div').after($textField);
        }
    }

    static markup({customFilter}) {

        return `
        <button type="button" class="btn btn-link js-back-to-list-button text-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${customFilter.label}*</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">            
            <div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator1" value="EQ" checked data-has-text-input="true">
                    <label class="form-check-label" for="editOperator1">
                     <p>is equal to</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator2" value="NEQ" data-has-text-input="true">
                    <label class="form-check-label" for="editOperator2">
                    <p>is not equal to</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator3" value="LT" checked data-has-text-input="true">
                    <label class="form-check-label" for="editOperator3">
                     <p>is less than</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator4" value="GT" data-has-text-input="true">
                    <label class="form-check-label" for="editOperator4">
                    <p>is greater than</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator5" value="BETWEEN" data-has-number-in-between-input="true">
                    <label class="form-check-label" for="editOperator5">
                    <p>is between</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator6" value="HAS_PROPERTY">
                    <label class="form-check-label" for="editOperator6">
                    <p>is known</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator7" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="editOperator7">
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
    <input type="text" name="value" class="form-control" autocomplete="off">
  </div>
    
`;

const numberInBetweenTemplate = () => `
  <div class="form-group js-operator-value">
    <input type="text" name="low_value" class="form-control" autocomplete="off">
    <span>and</span>
  </div>
  <div class="form-group js-operator-value">
    <input type="text" name="high_value" class="form-control" autocomplete="off">
  </div>
`;

export default EditNumberFieldFilterForm;