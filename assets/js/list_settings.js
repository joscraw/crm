import $ from 'jquery';

import EditRecord from "./Components/Page/EditRecord";
import SideNavigationMenu from "./Components/SideNavigationMenu";
import ReportSettings from "./Components/Page/ReportSettings";
import ReportWidget from "./Components/ReportWidget";
import EditReportWidget from "./Components/EditReportWidget";
import ListSettings from "./Components/Page/ListSettings";
import ListWidget from "./Components/ListWidget";
import EditListWidget from "./Components/EditListWidget";

require('backbone/backbone.js');

$(document).ready(function() {

    debugger;
    var Router = Backbone.Router.extend({
        routes: {
            ":internalIdentifier/lists": "index",
            ":internalIdentifier/lists/folders(/:folderId)": "folder",
            ":internalIdentifier/lists/create": "create",
            ":internalIdentifier/lists/:listId/edit": "edit"
        },

        index: function(internalIdentifier) {
            debugger;

            new ListSettings($('#app'), window.globalEventDispatcher, internalIdentifier);

            new SideNavigationMenu($('#side-nav'), window.globalEventDispatcher, internalIdentifier);

        },
        folder: function(internalIdentifier, folderId) {
            debugger;

            new ListSettings($('#app'), window.globalEventDispatcher, internalIdentifier, folderId, true);

            new SideNavigationMenu($('#side-nav'), window.globalEventDispatcher, internalIdentifier);

        },
        create: function(internalIdentifier) {
            debugger;
            $('#side-nav').html("");
            new ListWidget($('#app'), window.globalEventDispatcher, internalIdentifier);
        },
        edit: function(internalIdentifier, listId) {
            debugger;
            $('#side-nav').html("");
            debugger;
            new EditListWidget($('#app'), window.globalEventDispatcher, internalIdentifier, listId);
        }
    });

    var app_router = new Router;
    // Start Backbone history a necessary step for bookmarkable URL's
    Backbone.history.start({pushState: true});

    // All navigation that is relative should be passed through the navigate
    // method, to be processed by the router. If the link has a `data-bypass`
    // attribute, bypass the delegation completely.
    $(document).on("click", "a[href]:not([data-bypass])", function(evt) {
        debugger;
        // Get the absolute anchor href.
        var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
        // Get the absolute root.
        var root = location.protocol + "//" + location.host;

        // Ensure the root is part of the anchor href, meaning it's relative.
        if (href.prop.slice(0, root.length) === root) {
            // Stop the default event to ensure the link will not cause a page
            // refresh.
            evt.preventDefault();

            // `Backbone.history.navigate` is sufficient for all Routers and will
            // trigger the correct events. The Router's internal `navigate` method
            // calls this anyways.  The fragment is sliced from the root.
            Backbone.history.navigate(href.attr, true);
        }
    });

});