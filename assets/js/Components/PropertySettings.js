'use strict';

import $ from 'jquery';
import Settings from '../Settings';
import PropertySettingsTopBar from './PropertySettingsTopBar';


class PropertySettings {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.init($wrapper, globalEventDispatcher);
    }

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    init($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.render();
    }

    render() {

        const $topBar = this.$wrapper.find('.js-top-bar');

  /*      const container = document.createElement("div");
        document.body.appendChild(container);

        const $div = $("<div>", {"class": "js-top-bar"});
        $("#box").append($div);
*/
        new PropertySettingsTopBar($topBar, this.globalEventDispatcher);
    }

/*    static markup() {
        return `
        <div class="l-grid">
            <div class="l-grid__top-bar"></div>
            <div class="l-grid__main-content"></div>
        </div>
    `;
    }*/
}

export default PropertySettings;