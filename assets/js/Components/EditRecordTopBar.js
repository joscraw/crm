'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenCreatePropertyModalButton from './OpenCreatePropertyModalButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import EditPropertyBreadcrumbs from "./EditPropertyBreadcrumbs";
import EditRecordBreadcrumbs from "./EditRecordBreadcrumbs";


class EditRecordTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param recordId
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, recordId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.recordId = recordId;

        this.$wrapper.on(
            'keyup',
            '.js-property-value-search-input',
            this.handleKeyupEvent.bind(this)
        );

        this.render();
    }

    handleKeyupEvent(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        const searchObject = {
          searchValue: searchValue
        };

        this.globalEventDispatcher.publish(Settings.Events.PROPERTY_OR_VALUE_TOP_BAR_SEARCH_KEY_UP, searchObject);
    }

    render() {
        this.$wrapper.html(EditRecordTopBar.markup());

        new EditRecordBreadcrumbs(this.$wrapper.find('.js-edit-record-breadcrumbs'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.recordId);
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-edit-record-breadcrumbs"></div>
            <div class="col-md-6 js-top-bar-search-container">
                <div class="input-group c-search-control">
                  <input class="form-control c-search-control__input js-property-value-search-input" type="search" placeholder="Search for a property or value">
                  <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
                </div>
            </div>
        </div>
    `;
    }
}

export default EditRecordTopBar;