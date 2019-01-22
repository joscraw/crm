import $ from 'jquery';
import PropertySettings from './Components/Page/PropertySettings';
import EditProperty from "./Components/Page/EditProperty";
require('backbone/backbone.js');

$(document).ready(function() {

    var Router = Backbone.Router.extend({
        routes: {
            ":internalIdentifier/properties/:internalName": "index",
            ":internalIdentifier/properties/:internalName/:propertyInternalName": "propertyEdit",
        },

        index: function(internalIdentifier, internalName) {
            new PropertySettings($('#app'), window.globalEventDispatcher);
        },

        propertyEdit: function(internalIdentifier, internalName, propertyInternalName) {
            debugger;
            new EditProperty($('#app'), window.globalEventDispatcher, propertyInternalName);
            console.log("property edit page");
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