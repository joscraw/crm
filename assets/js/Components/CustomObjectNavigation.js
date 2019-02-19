'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";

class CustomObjectNavigation {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;

        this.loadCustomObjects().then(data => {
            debugger;
            this.render(data);
        })
    }

    render(data) {
        debugger;
        const $ul = $("<ul>", {"class": "nav nav-tabs c-tab-nav"});
        let customObjects = data.data.custom_objects;

        customObjects.forEach((customObject) => {

            let route = Routing.generate('property_settings', {internalIdentifier: this.portalInternalIdentifier, internalName: customObject.internalName});

            const html = pillTemplate(customObject, route);
            const $row = $($.parseHTML(html));
            $ul.append($row);

            if(this.customObjectInternalName === customObject.internalName) {

                $ul.find("[data-custom-object-id='" + customObject.id + "']").find('a').addClass('active');
            }

        });

        this.$wrapper.html($ul);
    }

    loadCustomObjects() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('' +
                'get_custom_objects', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url: url,
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }
}

/**
 * @param customObject
 * @param route
 * @return {string}
 */
const pillTemplate = (customObject, route) => `
   <li class="nav-item c-tab-nav__nav-item" data-custom-object-id="${customObject.id}">
     <a class="nav-link c-tab-nav__nav-link" href="${route}">${customObject.label}</a>
   </li>
`;

export default CustomObjectNavigation;