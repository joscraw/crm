'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import OpenCreatePropertyGroupModalButton from './OpenCreatePropertyGroupModalButton';
import OpenCreatePropertyModalButton from './OpenCreatePropertyModalButton';
import CustomObjectNavigation from './CustomObjectNavigation';
import Routing from "../Routing";


class EditRecordBreadcrumbs {

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

        debugger;

        this.render();
    }

    render() {

        let url = Routing.generate('record_list', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

        this.$wrapper.html(EditRecordBreadcrumbs.markup(url, this));
    }

    static markup(url, {customObjectInternalName, recordId}) {
        return `   
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="${url}">records</a></li>
                <li class="breadcrumb-item active" aria-current="page">${customObjectInternalName}</li>
                <li class="breadcrumb-item active" aria-current="page">${recordId}</li>
              </ol>
            </nav>
    `;
    }
}

export default EditRecordBreadcrumbs;