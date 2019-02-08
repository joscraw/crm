'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class EditSingleCheckboxFieldFilterForm {

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
            EditSingleCheckboxFieldFilterForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            EditSingleCheckboxFieldFilterForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            EditSingleCheckboxFieldFilterForm._selectors.backToListButton,
            this.handleBackButtonClicked.bind(this)
        );

        this.render();
       /* this.$wrapper.find('.js-radio-button').first().click();*/
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
        this.s = this.$wrapper.find('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            valueField: 'value',
            labelField: 'name',
            searchField: ['name'],
            options: [
                {value: 0, name: 'No'},
                {value: 1, name: 'Yes'}
            ],
        });
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', '#js-apply-filter-form');
        this.$wrapper.off('click', '.js-radio-button');
        this.$wrapper.off('click', EditSingleCheckboxFieldFilterForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        debugger;
        this.$wrapper.html(EditSingleCheckboxFieldFilterForm.markup(this));
        this.setRadioOption();
    }

    setRadioOption() {

        debugger;
        this.$wrapper.find('.js-radio-button').each((index, element) => {

            let value = $(element).val();
            let $radioButton = $(element);
            if(this.customFilter.operator === value) {

                this.activatePlugins();

                $(element).click();

                let values = this.customFilter.value.split(",");
                this.s.selectize()[0].selectize.setValue(values);
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

        this.globalEventDispatcher.publish(Settings.Events.APPLY_CUSTOM_FILTER_BUTTON_PRESSED, formData);
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
        }

        this.activatePlugins();
    }

    static markup({customFilter}) {

        return `
        <button type="button" class="btn btn-link js-back-to-list-button"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${customFilter.label}*</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">
            <input type="hidden" name="property" value="${customFilter.property}">
            <input type="hidden" name="fieldType" value="${customFilter.fieldType}">
            <input type="hidden" name="label" value="${customFilter.label}">
            <input type="hidden" name="id" value="${customFilter.id}">
            <div style="height: 200px; overflow-y: auto">
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator1" value="IN" checked data-has-text-input="true">
                    <label class="form-check-label" for="operator1">
                     <p>is any of</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="operator2" value="NOT_IN" data-has-text-input="true">
                    <label class="form-check-label" for="operator2">
                    <p>is none of</p>
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
    <input type="text" name="value" class="form-control js-selectize-multiple-select">
  </div>
    
`;

export default EditSingleCheckboxFieldFilterForm;