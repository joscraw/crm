'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import FormCollectionPrototypeUpdater from '../FormCollectionPrototypeUpdater';


class RecordEditForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, recordId) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.recordId = recordId;
        this.collapseStatus = {};


        this.$wrapper.on(
            'submit',
            RecordEditForm._selectors.editRecordForm,
            this.handleEditFormSubmit.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_OR_VALUE_TOP_BAR_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.$wrapper.on('click',
            RecordEditForm._selectors.collapseTitle,
            this.handleTitleClick.bind(this)
        );

        this.render();

        this.loadEditRecordForm().then(() => { this.activatePlugins(); });

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            editRecordForm: '.js-edit-record-form',
            collapse: '.js-collapse',
            collapseTitle: '.js-collapse__title',
            collapseBody: '.js-collapse__body',
            forms: '.js-forms',
            noResults: '.js-no-results'
        }
    }

    render() {
        const html = mainTemplate();
        const $mainTemplate = $($.parseHTML(html));
        this.$wrapper.html($mainTemplate);
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        let $collapsePanels = this.$wrapper.find('.js-collapse');
        let numOfPanels = $collapsePanels.length;
        let numOfPanelsHidden = 0;

        $collapsePanels.each((index, element) => {
            debugger;
            let $formItems = $(element).find('.js-form-item');
            let showPanel = false;

            if(this.searchValue.trim() === '') {

                $(element).find(RecordEditForm._selectors.collapseBody).removeClass('show');

                $(element).find(RecordEditForm._selectors.collapseTitle).find('i').removeClass('is-active');

                if($(element).hasClass('d-none')) {
                    $(element).removeClass('d-none');
                }
            }

            $formItems.each((index, element) => {

                debugger;
                let $search = $(element).find('.js-search-item');
                let label = $search.data('label');
                let value = $search.data('value');

                if(this.searchValue.trim() === '') {

                    if($(element).hasClass('d-none')) {

                        $(element).removeClass('d-none');
                    }

                    return true;
                }

                if (Array.isArray(value)) {
                    value = JSON.stringify(value);
                } else if(Number.isInteger(value)) {
                    value = value.toString();
                }

                if((value === null || value.toLowerCase().indexOf(this.searchValue.toLowerCase()) === -1) && (label === null || label.toLowerCase().indexOf(this.searchValue.toLowerCase()) === -1)) {
                    debugger;
                    if(!$(element).hasClass('d-none')) {

                        $(element).addClass('d-none');
                    }

                } else {
                    if($(element).hasClass('d-none')) {

                        $(element).removeClass('d-none');
                    }

                    showPanel = true;
                }

            });

            if(showPanel) {

                $(element).find(RecordEditForm._selectors.collapseBody).addClass('show');

                $(element).find(RecordEditForm._selectors.collapseTitle).find('i').addClass('is-active');
            } else {

                $(element).find(RecordEditForm._selectors.collapseBody).removeClass('show');

                $(element).find(RecordEditForm._selectors.collapseTitle).find('i').removeClass('is-active');

                numOfPanelsHidden++;
            }

            if(this.searchValue.trim() !== '' && numOfPanelsHidden === numOfPanels) {

                if(!this.$wrapper.find(RecordEditForm._selectors.forms).hasClass('d-none')) {
                    this.$wrapper.find(RecordEditForm._selectors.forms).addClass('d-none');
                }

                if(this.$wrapper.find(RecordEditForm._selectors.noResults).hasClass('d-none')) {
                    this.$wrapper.find(RecordEditForm._selectors.noResults).removeClass('d-none');
                }

            } else {

                if(this.$wrapper.find(RecordEditForm._selectors.forms).hasClass('d-none')) {
                    this.$wrapper.find(RecordEditForm._selectors.forms).removeClass('d-none');
                }

                if(!this.$wrapper.find(RecordEditForm._selectors.noResults).hasClass('d-none')) {
                    this.$wrapper.find(RecordEditForm._selectors.noResults).addClass('d-none');
                }

            }

            if($(element).find('.js-form-item').not('.d-none').length === 0) {
                if(!$(element).hasClass('d-none')) {
                    $(element).addClass('d-none');
                }
            } else {
                if($(element).hasClass('d-none')) {
                    $(element).removeClass('d-none');
                }
            }

        });

    }

    openPanelsWithErrors() {

        let $collapseBody = $('.form-error-message').closest(RecordEditForm._selectors.collapse)
            .find(RecordEditForm._selectors.collapseBody);


        $collapseBody.collapse('toggle');

    }

    handleTitleClick(e) {

        debugger;
        let $collapseBody = $(e.target).closest(RecordEditForm._selectors.collapse)
            .find(RecordEditForm._selectors.collapseBody);

        let $collapseTitle = $(e.target).closest(RecordEditForm._selectors.collapse)
            .find(RecordEditForm._selectors.collapseTitle);

        let propertyGroupId = $(e.target).closest(RecordEditForm._selectors.collapse).data('property-group-id');

        $collapseBody.on('hidden.bs.collapse', (e) => {
            this.collapseStatus[propertyGroupId] = 'hide';
        });

        $collapseBody.on('shown.bs.collapse', (e) => {
            this.collapseStatus[propertyGroupId] = 'show';
        });

        $collapseBody.on('show.bs.collapse', (e) => {
            $collapseTitle.find('i').addClass('is-active');
        });

        $collapseBody.on('hide.bs.collapse', (e) => {
            $collapseTitle.find('i').removeClass('is-active');
        });

        $collapseBody.collapse('toggle');
    }


    activatePlugins() {


        $('.js-selectize-multiple-select').selectize({
            plugins: ['remove_button'],
            sortField: 'text'
        });

        $('.js-selectize-single-select').selectize({
            sortField: 'text'
        });

        debugger;

        const url = Routing.generate('records_for_selectize', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

        debugger;

        $('.js-selectize-single-select-with-search').each((index, element) => {

            let select = $(element).selectize({
                valueField: 'valueField',
                labelField: 'labelField',
                searchField: 'searchField',
                load: (query, callback) => {

                    if (!query.length) return callback();
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            search: query,
                            allowed_custom_object_to_search: $(element).data('allowedCustomObjectToSearch'),
                            property_id: $(element).data('propertyId')
                        },
                        error: () => {
                            callback();
                        },
                        success: (res) => {
                            select.selectize()[0].selectize.clearOptions();
                            select.options = res;
                            callback(res);
                        }
                    })
                }
            });


        });

        $('.js-datepicker').datepicker({
            format: 'mm/dd/yyyy'
        });
    }

    loadEditRecordForm() {
        return new Promise((resolve, reject) => {
            debugger;
            $.ajax({
                url: Routing.generate('edit_record_form', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, recordId: this.recordId}),
            }).then(data => {
                this.$wrapper.find(RecordEditForm._selectors.forms).html(data.formMarkup);
                resolve(data);
            }).catch(errorData => {
                reject(errorData);
            });
        });
    }

    /**
     * @param e
     */
    handleEditFormSubmit(e) {

        debugger;

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._editRecord(formData)
            .then((data) => {
                swal("Sweeeeet!", "You've edited your Record!", "success");
                this.globalEventDispatcher.publish(Settings.Events.PROPERTY_EDITED);
            }).catch((errorData) => {

                if(errorData.httpCode === 401) {
                    swal("Woah!", `You don't have proper permissions for this!`, "error");
                    return;
                }

                this.$wrapper.find(RecordEditForm._selectors.forms).html(errorData.formMarkup);

                this.openPanelsWithErrors();

                this.activatePlugins();

        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _editRecord(data) {
        return new Promise( (resolve, reject) => {
            debugger;
            const url = Routing.generate('edit_record', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName, recordId: this.recordId});

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;

                reject(errorData);
            });
        });
    }
}

const mainTemplate = () => `
    <div class="js-forms"></div>
    <div class="js-no-results d-none">
        <h1 style="text-align: center; margin-top: 100px">No properties or values found...</h1>
    </div>
`;

export default RecordEditForm;