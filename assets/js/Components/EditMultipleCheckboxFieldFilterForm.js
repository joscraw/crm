'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class EditMultipleCheckboxFieldFilterForm {

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
            EditMultipleCheckboxFieldFilterForm._selectors.radioButton,
            this.handleOperatorRadioButtonClicked.bind(this)
        );

        this.$wrapper.on(
            'submit',
            EditMultipleCheckboxFieldFilterForm._selectors.applyFilterForm,
            this.handleNewFilterFormSubmit.bind(this)
        );

        this.$wrapper.on(
            'click',
            EditMultipleCheckboxFieldFilterForm._selectors.backToListButton,
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

        debugger;
        let options = [];
        for(let i = 0; i < this.customFilter.field.options.length; i++) {
            let option = this.customFilter.field.options[i];
            options.push({value: option.label, name: option.label});
        }

        debugger;
        this.s = $('.js-edit-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            valueField: 'value',
            labelField: 'name',
            searchField: ['name'],
            options: options
        });
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('submit', '#js-apply-filter-form');
        this.$wrapper.off('click', '.js-radio-button');
        this.$wrapper.off('click', EditMultipleCheckboxFieldFilterForm._selectors.backToListButton);
    }

    handleBackButtonClicked() {
        this.globalEventDispatcher.publish(Settings.Events.FILTER_BACK_TO_LIST_BUTTON_CLICKED);
    }

    render() {
        debugger;
        this.$wrapper.html(EditMultipleCheckboxFieldFilterForm.markup(this));
        this.setRadioOption();
    }

    setRadioOption() {

        debugger;
        this.$wrapper.find('.js-radio-button').each((index, element) => {

            debugger;
            let value = $(element).val();
            if(this.customFilter.operator === value) {

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

        const customFilter = {...this.customFilter, ...formData};

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

             this.activatePlugins();
        }

    }

    static markup({customFilter}) {

        return `
        <button type="button" class="btn btn-link js-back-to-list-button"><i class="fa fa-chevron-left"></i> Back</button>
        <p><small>${customFilter.label}*</small></p>
        <form name="filter" id="js-apply-filter-form" novalidate="novalidate">
            <div style="height: 200px; overflow-y: auto">
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator1" value="IN" checked data-has-text-input="true">
                    <label class="form-check-label" for="editOperator1">
                     <p>is any of</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator2" value="NOT_IN" data-has-text-input="true">
                    <label class="form-check-label" for="editOperator2">
                    <p>is none of</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator3" value="HAS_PROPERTY">
                    <label class="form-check-label" for="editOperator3">
                    <p>is known</p>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input js-radio-button" type="radio" name="operator" id="editOperator4" value="NOT_HAS_PROPERTY">
                    <label class="form-check-label" for="editOperator4">
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
    <input type="text" name="value" class="form-control js-edit-selectize-multiple-select">
  </div>
    
`;

export default EditMultipleCheckboxFieldFilterForm;