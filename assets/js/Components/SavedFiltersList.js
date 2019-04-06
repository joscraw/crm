'use strict';

import $ from 'jquery';
import swal from 'sweetalert2';
import Routing from '../Routing';
import Settings from '../Settings';
import List from 'list.js';
import ColumnSearch from "./ColumnSearch";
import SavedFilterSearch from "./SavedFilterSearch";
require('jquery-ui-dist/jquery-ui');

class SavedFiltersList {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     * @param customObjectInternalName
     */
    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, customObjectInternalName) {

        debugger;
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.customObjectInternalName = customObjectInternalName;
        this.searchValue = '';
        this.list = null;
        this.savedFilters = {};
        this.savedfilterToApply = null;

        this.globalEventDispatcher.subscribe(
            Settings.Events.SAVED_FILTER_SEARCH_KEY_UP,
            this.applySearch.bind(this)
        );

        this.unbindEvents();

        this.$wrapper.on(
            'click',
            SavedFiltersList._selectors.savedFilterListItem,
            this.handleSavedFilterListItemClicked.bind(this)
        );

        this.$wrapper.on(
            'click',
            SavedFiltersList._selectors.applySavedFilterButton,
            this.handleApplySavedFilterButtonClicked.bind(this)
        );

        this.loadSavedFilters().then(data => {

            this.savedFilters = data.data;

            this.render(this.savedFilters).then(() => {

                this.highlightFirstListItem();

            })
        });

    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            savedFilterListItem: '.js-saved-filter-list-item',
            applySavedFilterButton: '.js-apply-saved-filter-button'
        }
    }

    /**
     * Because this component can keep getting run each time a filter is added
     * you need to remove the handlers otherwise they will keep stacking up
     */
    unbindEvents() {
        this.$wrapper.off('click', SavedFiltersList._selectors.savedFilterListItem);
        this.$wrapper.off('click', SavedFiltersList._selectors.applySavedFilterButton);

    }

    highlightFirstListItem() {

        if(this.savedFilters.length === 0) {
            return;
        }

        this.savedfilterToApply = this.savedFilters[0];

        $(SavedFiltersList._selectors.savedFilterListItem).first().addClass('c-list__list-item--active');
    }

    handleSavedFilterListItemClicked(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $listItem = $(e.currentTarget);

        $(SavedFiltersList._selectors.savedFilterListItem).each((index, element) => {

            if ($(element).hasClass('c-list__list-item--active')) {
                $(element).removeClass('c-list__list-item--active');
            }

        });

        $listItem.addClass('c-list__list-item--active');

        let savedFilterIndex = $listItem.attr('data-saved-filter-index');

        this.savedfilterToApply = null;
        this.savedfilterToApply = this.savedFilters[savedFilterIndex];
    }

    handleApplySavedFilterButtonClicked(e) {

        debugger;
        if(e.cancelable) {
            e.preventDefault();
        }

        this.globalEventDispatcher.publish(Settings.Events.APPLY_SAVED_FILTER_BUTTON_CLICKED, this.savedfilterToApply.customFilters);

        debugger;
    }

    /**
     * @param args
     */
    applySearch(args = {}) {

        if(typeof args.searchValue !== 'undefined') {
            this.searchValue = args.searchValue;
        }

        this.list.search(this.searchValue);

    }

    loadSavedFilters() {
        return new Promise((resolve, reject) => {
            debugger;
            const url = Routing.generate('saved_filters', {internalIdentifier: this.portalInternalIdentifier, internalName: this.customObjectInternalName});

            $.ajax({
                url: url
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }

    render(savedFilters) {
        return new Promise((resolve, reject) => {

            debugger;
            const html = mainTemplate();
            const $mainTemplate = $($.parseHTML(html));
            this.$wrapper.append($mainTemplate);

            this._addList(savedFilters);

            debugger;
            new SavedFilterSearch(this.$wrapper.find('.js-saved-filter-search-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName, "Search for a filter...");

            resolve();
        });
    }

    /**
     * @private
     * @param filters
     */
    _addList(filters) {

        debugger;

        const html = listTemplate();
        const $list = $($.parseHTML(html));
        this.$wrapper.find('.js-saved-filter-list').append($list);

        // List.js is used to render the list on the left and to allow searching of said list
        let options = {
            valueNames: [ 'name' ],

            item: `<li class="js-saved-filter-list-item c-list__list-item"><span class="name"></span></li>`
        };

        this.list = new List('saved-filter-list', options, filters);

        $( `#saved-filter-list li` ).each((index, element) => {
            $(element).attr('data-saved-filter-index', index);
        });

    }

}

const listTemplate = () => `
    <div id="saved-filter-list" class="js-list">
      <ul class="list c-list"></ul>
    </div>
    
`;

const mainTemplate = () => `
    <div class="row c-column-editor">
        <div class="col-md-12">
            <div class="js-saved-filter-search-container c-column-editor__search-container"></div>
            <div class="js-saved-filter-list c-column-editor__property-list"></div>
        </div>
        
        <div class="col-md-12 c-column-editor__footer">
            <form class="js-selected-properties-form">
                <input type="hidden" value="" class="js-sorted-properties" name="sortedProperties">
                <button type="submit" class="btn-primary btn w-100 js-apply-saved-filter-button">Apply Filter</button>
            </form>
        </div>
      
    </div>
`;


export default SavedFiltersList;