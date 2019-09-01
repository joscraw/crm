'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";
import StringHelper from "../StringHelper";

class SingleLineTextFieldFilterForm {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, property) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.property = property;

        this.unbindEvents();

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

        this.setupFormAttributes();

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
        this.$wrapper.html(SingleLineTextFieldFilterForm.markup(this));
        this.$wrapper.find('.js-radio-button').first().click();
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
    }

    static markup({property, operator1, operator2, operator3, operator4}) {

        return `
        <button type="button" class="btn btn-link js-back-to-list-button text-left" style="padding:0"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${property.label}*</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">
            <div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="${operator1}" value="EQ" checked data-has-text-input="true">
                    <label class="form-check-label" for="${operator1}">
                     <p>contains exactly</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="${operator2}" value="NEQ" data-has-text-input="true">
                    <label class="form-check-label" for="${operator2}">
                    <p>doesn't contain exactly</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="${operator3}" value="HAS_PROPERTY">
                    <label class="form-check-label" for="${operator3}">
                    <p>is known</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="${operator4}" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="${operator4}">
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

export default SingleLineTextFieldFilterForm;