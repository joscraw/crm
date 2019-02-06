'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class EditSingleLineTextFieldFilter {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, customFilter) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.customFilter = customFilter;

        /*this.unbindEvents();*/

/*        this.$wrapper.on(
            'click',
            '.js-radio-button',
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            '#js-apply-filter-form',
            this.handleNewFilterFormSubmit.bind(this)
        );*/

        this.render();
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', '#js-apply-filter-form');
        this.$wrapper.off('click', '.js-radio-button');
    }

    render() {
        debugger;
        let $operator = this.$wrapper.find(`input[value="${this.customFilter['operator']}"]`);
        this.$wrapper.html(EditSingleLineTextFieldFilter.markup(this));

        const html = textFieldTemplate();
        const $textField = $($.parseHTML(html));
        $operator.closest('div').after($textField);

        /*this.$wrapper.find('.js-radio-button').first().click();*/
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

        this.globalEventDispatcher.publish(Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED, formData);
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

    static markup({customFilter}) {

        debugger;
        return `
        <p><small>${customFilter.label}</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate" class="test">
            <input type="hidden" name="property" value="${customFilter.property}">
            <input type="hidden" name="fieldType" value="${customFilter.fieldType}">
            <input type="hidden" name="label" value="${customFilter.label}">
            <input type="hidden" name="id" value="${customFilter.id}">
            <div style="height: 200px; overflow-y: auto">
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator1" value="EQ" checked data-has-text-input="true">
                    <label class="form-check-label" for="operator1">
                     <p>contains exactly</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator2" value="NEQ" data-has-text-input="true">
                    <label class="form-check-label" for="operator2">
                    <p>doesn't contain exactly</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator3" value="HAS_PROPERTY">
                    <label class="form-check-label" for="operator3">
                    <p>is known</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator4" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="operator4">
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

export default EditSingleLineTextFieldFilter;