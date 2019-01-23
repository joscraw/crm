'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";

class CustomObjectNavigation {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     * @param customObject
     * @param internalIdentifier
     * @param internalName
     */
    constructor($wrapper, globalEventDispatcher, portal, customObject, internalIdentifier, internalName) {

        debugger;
        this.portal = portal;
        this.customObject = customObject;
        this.$wrapper = $wrapper;
        this.internalIdentifier = internalIdentifier;
        this.internalName = internalName;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.loadCustomObjects().then(data => {
            this.render(data);
        })
    }

    render(data) {
        const $ul = $("<ul>", {"class": "nav nav-tabs c-tab-nav"});
        for(let key in data.data.custom_objects) {
            if(data.data.custom_objects.hasOwnProperty(key)) {

                let customObject = data.data.custom_objects[key];
                let route = Routing.generate('property_settings', {internalIdentifier: this.portal, internalName: customObject.internalName});

                const html = pillTemplate(customObject, route);
                const $row = $($.parseHTML(html));
                $ul.append($row);

                if(this.internalName === customObject.internalName) {

                    $ul.find("[data-custom-object-id='" + customObject.id + "']").find('a').addClass('active');
                }

            }
        }

        this.$wrapper.html($ul);
    }

    loadCustomObjects() {
        return new Promise((resolve, reject) => {
            let url = Routing.generate('get_custom_objects', {internalIdentifier: this.portal});

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