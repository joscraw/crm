import $ from 'jquery';
import PropertySettings from './Components/PropertySettings';

$(document).ready(function() {
    new PropertySettings($('.js-property-settings'), window.globalEventDispatcher);
});