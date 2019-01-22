import $ from 'jquery';
import RecordList from './Components/Page/RecordList';

require('backbone/backbone.js');

$(document).ready(function() {
    /*new RecordList($('#app'), window.globalEventDispatcher);*/

    debugger;
    var Router = Backbone.Router.extend({
        routes: {
            "settings": "index",
            "properties": "search"
        },

        index: function() {
            debugger;

            console.log("hi");
        },

        search: function() {
            debugger;
            console.log("bye");
        }
    });

    var app_router = new Router;
// Start Backbone history a necessary step for bookmarkable URL's
    Backbone.history.start(/*{pushState: true}*/);

});