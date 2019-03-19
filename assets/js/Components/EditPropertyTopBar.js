'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenCreatePropertyModalButton from './OpenCreatePropertyModalButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import Routing from "../Routing";
import EditPropertyBreadcrumbs from "./EditPropertyBreadcrumbs";


class EditPropertyTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     * @param propertyInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, propertyInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.propertyInternalName = propertyInternalName;

        this.render();
    }

    render() {

        debugger;
        this.$wrapper.html(EditPropertyTopBar.markup(this));

        new EditPropertyBreadcrumbs(this.$wrapper.find('.js-edit-property-breadcrumbs'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, this.propertyInternalName);
    }

    static markup() {
        return `
        <div class="row">
            <div class="col-md-6 js-edit-property-breadcrumbs"></div>
            <div class="col-md-6 text-right js-top-bar-button-container"></div>
        </div>
    `;
    }
}

export default EditPropertyTopBar;