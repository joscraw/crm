'use strict';

import Routing from '../Routing';
import Settings from '../Settings';
import $ from "jquery";

class CustomObjectNavigation {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param children
     */
    constructor($wrapper, globalEventDispatcher, children = {}) {

        this.children = children;
        this.$wrapper = $wrapper;

        /**
         * @type {EventDispatcher}
         */
        this.globalEventDispatcher = globalEventDispatcher;

        this.loadCustomObjects().then(data => {
            this.render(data);
        })
    }

    render(data) {
        const $ul = $("<ul>", {"class": "nav nav-tabs"});
        for(let key in data.data.custom_objects) {
            if(data.data.custom_objects.hasOwnProperty(key)) {
                let customObject = data.data.custom_objects[key];
                const html = pillTemplate(customObject);
                const $row = $($.parseHTML(html));
                $ul.append($row);
            }
        }
        this.$wrapper.html($ul);
    }

    loadCustomObjects() {
        return new Promise((resolve, reject) => {
            const url = Routing.generate('get_custom_objects', {portal: this.children.propertySettings.portal});

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
 * @return {string}
 */
const pillTemplate = (customObject) => `
   <li class="nav-item">
     <a class="nav-link active" href="#">${customObject.label}</a>
   </li>
`;

export default CustomObjectNavigation;