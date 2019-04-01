'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenCreatePropertyModalButton from './OpenCreatePropertyModalButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import Routing from "../Routing";


class EditPropertyBreadcrumbs {

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

        debugger;

        this.render();
    }

    render() {

        let url = Routing.generate('property_settings', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

        this.$wrapper.html(EditPropertyBreadcrumbs.markup(url, this));
    }

    static markup(url, {customObjectInternalName, propertyInternalName}) {
        return `   
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="${url}">properties</a></li>
                <li class="breadcrumb-item active" aria-current="page">${customObjectInternalName}</li>
                <li class="breadcrumb-item active" aria-current="page">${propertyInternalName}</li>
              </ol>
            </nav>
    `;
    }
}

export default EditPropertyBreadcrumbs;