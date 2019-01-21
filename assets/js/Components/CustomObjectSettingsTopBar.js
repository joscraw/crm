'use strict';

import $ from 'jquery';
import Settings from '../Settings';

import OpenCreateCustomObjectModalButton from './OpenCreateCustomObjectModalButton';
import CustomObjectSearch from "./CustomObjectSearch";


class CustomObjectSettingsTopBar {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portal
     */
    constructor($wrapper, globalEventDispatcher, portal) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portal = portal;

        this.render();
    }

    render() {
        this.$wrapper.html(CustomObjectSettingsTopBar.markup());
        new CustomObjectSearch(this.$wrapper.find('.js-top-bar-search-container'), this.globalEventDispatcher, this.portal, "Search for a custom object");
        new OpenCreateCustomObjectModalButton(this.$wrapper.find('.js-create-custom-object-button'), this.globalEventDispatcher, this.portal);
    }

        static markup() {
            return `
            <div class="row">  
                <div class="col-md-6 js-top-bar-search-container"></div>
                <div class="col-md-6 text-right">
                    <div class="js-create-custom-object-button d-inline-block"></div>     
                </div>
            </div>
        `;
        }
}

export default CustomObjectSettingsTopBar;