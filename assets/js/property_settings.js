import $ from 'jquery';
import PropertySettings from './Components/Page/PropertySettings';

$(document).ready(function() {
    new PropertySettings($('.js-property-settings'), window.globalEventDispatcher);
});