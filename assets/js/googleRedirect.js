import $ from 'jquery';
import Routing from './Routing';

$(document).ready(function() {
    console.log("google redirect");
    window.location = Routing.generate('oauth_google_after_redirect_code');
});