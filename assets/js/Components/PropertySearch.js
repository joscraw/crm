'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import $ from "jquery";

class PropertySearch {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName, placeholderText) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.placeholderText = placeholderText;

        this.$wrapper.on(
            'keyup',
            '.js-search',
            this.handleKeyupEvent.bind(this)
        );

        this.render();
    }

    handleKeyupEvent(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const searchValue = $(e.target).val();
        const searchObject = {
            searchValue: searchValue
        };

        this.globalEventDispatcher.publish(Settings.Events.PROPERTY_SEARCH_KEY_UP, searchObject);
    }

    render() {
        this.$wrapper.html(PropertySearch.markup(this));
    }

    static markup({placeholderText}) {

        return `
            <div class="input-group c-search-control">
              <input class="form-control c-search-control__input js-search" type="search" placeholder="${placeholderText}">
              <span class="c-search-control__foreground"><i class="fa fa-search"></i></span>
            </div>
        `;
    }
}

export default PropertySearch;