'use strict';

import Settings from '../Settings';
import RecordFormModal from './RecordFormModal';
import CreateRecordButton from "./CreateRecordButton";
import EditColumnsModal from "./EditColumnsModal";
import EditDefaultPropertiesModal from "./EditDefaultPropertiesModal";
import Routing from "../Routing";
import $ from "jquery";

class ListFolderBreadcrumbs {

    constructor($wrapper, globalEventDispatcher, portalInternalIdentifier, showListFolderTable, folderId) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;
        this.showListFolderTable = showListFolderTable;
        this.folderId = folderId;

        this.render();
    }

    render() {

        this.loadBreadcrumbs().then(data => {
            debugger;

            this.$wrapper.html(data.data);
        })
    }

    loadBreadcrumbs() {
        return new Promise((resolve, reject) => {

            let data = {};

            if(this.folderId) {
                data.folderId = this.folderId;
            }

            let url = Routing.generate('' +
                'list_folder_breadcrumbs', {internalIdentifier: this.portalInternalIdentifier});

            $.ajax({
                url: url,
                data: data
            }).then(data => {
                resolve(data);
            }).catch(jqXHR => {
                const errorData = JSON.parse(jqXHR.responseText);
                reject(errorData);
            });
        });
    }
}

export default ListFolderBreadcrumbs;