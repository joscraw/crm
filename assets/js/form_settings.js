import $ from 'jquery';

import EditRecord from "./Components/Page/EditRecord";
import SideNavigationMenu from "./Components/SideNavigationMenu";
import ReportSettings from "./Components/Page/ReportSettings";
import ReportWidget from "./Components/ReportWidget";
import EditReportWidget from "./Components/EditReportWidget";
import FormSettings from "./Components/Page/FormSettings";
import FormSelectObject from "./Components/FormSelectObject";
import FormEditorEditForm from "./Components/FormEditorEditForm";
import Form from "./Components/Form";

require('backbone/backbone.js');

$(document).ready(function() {

    var Router = Backbone.Router.extend({
        routes: {
            ":internalIdentifier/forms": "index",
            ":internalIdentifier/forms/object": "object",
            ":internalIdentifier/forms/editor/:uid/edit/form": "editorEditForm",
            "forms/:uid": "form"
        },

        index: function(internalIdentifier) {
            new FormSettings($('#app'), window.globalEventDispatcher, internalIdentifier);
            new SideNavigationMenu($('#side-nav'), window.globalEventDispatcher, internalIdentifier);
        },
        editorEditForm: function(internalIdentifier, uid) {
            new FormEditorEditForm($('#app'), window.globalEventDispatcher, internalIdentifier, uid);
        },
        object: function(internalIdentifier) {
            new FormSelectObject($('#app'), window.globalEventDispatcher, internalIdentifier);
        },
        form: function(uid) {
            debugger;
            new Form($('#app'), window.globalEventDispatcher, uid);
        },
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