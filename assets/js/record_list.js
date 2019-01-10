import $ from 'jquery';
import RecordList from './Components/RecordList';

$(document).ready(function() {
    new RecordList($('.js-record-list'), window.globalEventDispatcher);
});