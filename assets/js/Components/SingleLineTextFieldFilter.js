'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class SingleLineTextFieldFilter {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, property) {
        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.property = property;

        this.$wrapper.on(
            'click',
            '.js-radio-button',
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            '.js-apply-filter-form',
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.render();
    }

    render() {
        this.$wrapper.html(SingleLineTextFieldFilter.markup(this));

        this.$wrapper.find('.js-radio-button').first().click();
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

        console.log("Custom filter added.");
        this.globalEventDispatcher.publish(Settings.Events.CUSTOM_FILTER_ADDED, formData);
        console.log(`Event Dispatched: ${Settings.Events.CUSTOM_FILTER_ADDED}`);
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

    static markup({property}) {

        return `
        <form name="filter" method="post" class="js-apply-filter-form" novalidate="novalidate">
            <input type="hidden" name="property" value="${property.internalName}">
            <input type="hidden" name="fieldType" value="${property.fieldType}">
            <div style="height: 200px; overflow-y: auto">
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator1" value="EQ" checked data-has-text-input="true">
                    <label class="form-check-label" for="operator1">
                     contains exactly
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator2" value="NEQ" data-has-text-input="true">
                    <label class="form-check-label" for="operator2">
                    doesn't contain exactly
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator3" value="HAS_PROPERTY">
                    <label class="form-check-label" for="operator3">
                    is known
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator4" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="operator4">
                    is unknown
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

export default SingleLineTextFieldFilter;