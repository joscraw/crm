'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";
import EditCustomObjectButton from "./EditCustomObjectButton";
import EditPropertyGroupButton from "./EditPropertyGroupButton";
import DeletePropertyGroupButton from "./DeletePropertyGroupButton";
import DeleteCustomObjectButton from "./DeleteCustomObjectButton";
import DeletePropertyButton from "./DeletePropertyButton";

require( 'datatables.net-bs4' );
require( 'datatables.net-responsive-bs4' );
require( 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css' );
require( 'datatables.net-bs4/css/dataTables.bootstrap4.css' );


class PropertyList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        debugger;

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.searchValue = '';
        this.collapseStatus = {};



        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_CREATED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_CREATED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_EDITED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_EDITED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_GROUP_DELETED,
            this.redrawDataTable.bind(this)
        );

        this.globalEventDispatcher.subscribe(
            Settings.Events.PROPERTY_DELETED,
            this.redrawDataTable.bind(this)
        );

        this.$wrapper.on('click',
            PropertyList._selectors.collapseTitle,
            this.handleTitleClick.bind(this)
            );

        this.loadProperties().then(data => {
            this.render(data);
        })
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            collapse: '.js-collapse',
            collapseTitle: '.js-collapse__title',
            collapseBody: '.js-collapse__body'
        }
    }

    handleTitleClick(e) {

        let $collapseBody = $(e.target).closest(PropertyList._selectors.collapse)
            .find(PropertyList._selectors.collapseBody);

        let $collapseTitle = $(e.target).closest(PropertyList._selectors.collapse)
            .find(PropertyList._selectors.collapseTitle);

        let propertyGroupId = $(e.target).closest(PropertyList._selectors.collapse).data('property-group-id');

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

    render(data) {
        this.$wrapper.html("");
        for(let key in data.data.property_groups) {
            if(data.data.property_groups.hasOwnProperty(key)) {
                let propertyGroup = data.data.property_groups[key];
                let properties = data.data.properties[key];
                this._addRow(propertyGroup, properties);
            }
        }
    }

    applyCollapseStatus() {
        for(let propertyGroupId in this.collapseStatus) {
            let status;
            if(this.collapseStatus.hasOwnProperty(propertyGroupId)) {
                debugger;
                status = this.collapseStatus[propertyGroupId];
            }
            let $collapseBody = $("div").find(`[data-property-group-id='${propertyGroupId}']`).closest(PropertyList._selectors.collapse)
                .find(PropertyList._selectors.collapseBody);

            if(status === 'show') {
                $collapseBody.addClass('show');
            }
        }
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        $('table').DataTable().search(
            this.searchValue
        ).draw();

        this.$wrapper.find('.js-collapse').each((index, element) => {
            if($(element).find('.dataTables_empty').length && this.searchValue !== '') {
                $(element).addClass('is-disabled');

            } else {
                if($(element).hasClass('is-disabled')) {
                    $(element).removeClass('is-disabled');
                }
            }
        });
    }

    redrawDataTable() {
        this.loadProperties().then(data => {
            this.render(data);
            this.applyCollapseStatus();
            this.applySearch();
        });
    }

    loadProperties() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('properties_for_datatable', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    /**
     * @param propertyGroup
     * @param properties
     * @private
     */
    _addRow(propertyGroup, properties) {
        const html = rowTemplate(propertyGroup);
        const $row = $($.parseHTML(html));
        this.$wrapper.append($row);

        $('#table' + propertyGroup.id).DataTable({
            "paging": false,
            "destroy": true,
            "responsive": true,
            "searching":true,
            "language": {
                "emptyTable": "No properties.",
            },
            /*
            the "dom" property determines what components DataTables shows by default

            Possible Flags:

            l - length changing input control
            f - filtering input
            t - The table!
            i - Table information summary
            p - pagination control
            r - processing display element

            For more information on the "dom" property and how to use it
            https://datatables.net/reference/option/dom
            */
            "dom": "rt",
            "columns": [
                { "data": "label", "name": "label", "title": "label", mRender: (data, type, row) => {

                        let url = Routing.generate('property_settings', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});
                        url = `${url}/${row['internalName']}`;

                        return `
                        ${row['label']} <span class="c-table__edit-button"><a href="${url}" role="button" class="btn btn-primary btn-sm">Edit</a></span>
                        <span class="js-delete-property c-table__delete-button" data-property-internal-name="${row['internalName']}"></span>
                         `;

                    } },
                //repeat for each of my 20 or so fields
            ],
            "data": properties,
            "initComplete": (settings, json) => {
                $row.find('.js-delete-property').each((index, element) => {
                    new DeletePropertyButton($(element), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, $(element).attr('data-property-internal-name'), "Delete");
                });
            }
        });

        debugger;
        new EditPropertyGroupButton($row.find('.js-edit-property-group-button'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, propertyGroup.internalName, "Edit");
        new DeletePropertyGroupButton($row.find('.js-delete-property-group-button'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, propertyGroup.internalName, "Delete");
    }
}

/**
 * @param propertyGroup
 * @return {string}
 */
const rowTemplate = (propertyGroup) => `
    <div class="c-collapse js-collapse" data-property-group-id="${propertyGroup.id}">
        <div class="is-active c-collapse__title js-collapse__title clearfix">
        <h2 class="c-collapse__header"><i class="fa fa-angle-right c-collapse__title-icon"></i> ${propertyGroup.label}</h2>
          <div class="d-inline js-delete-property-group-button c-collapse__delete-property-group-button"></div>
          <div class="d-inline js-edit-property-group-button c-collapse__edit-property-group-button"></div>  
        </div>
        <div class="collapse c-collapse__body js-collapse__body">
          <div class="card card-body">
            <table id="table${propertyGroup.id}" class="table table-striped table-bordered c-table" style="width:100%">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
          </div>
        </div>  
    </div>
`;

export default PropertyList;