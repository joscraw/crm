'use strict';

const Settings = {

    Events: {
        CREATE_CUSTOM_OBJECT_BUTTON_CLICKED: 'CREATE_CUSTOM_OBJECT_BUTTON_CLICKED',
        CUSTOM_OBJECT_CREATED: 'CUSTOM_OBJECT_CREATED',
        PROPERTY_GROUP_CREATED: 'PROPERTY_GROUP_CREATED',
        CREATE_PROPERTY_GROUP_BUTTON_CLICKED: 'CREATE_PROPERTY_GROUP_BUTTON_CLICKED',
        CREATE_PROPERTY_BUTTON_CLICKED: 'CREATE_PROPERTY_BUTTON_CLICKED',
        PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP: 'PROPERTY_SETTINGS_TOP_BAR_SEARCH_KEY_UP',
        PROPERTY_CREATED: 'PROPERTY_CREATED',
        CREATE_RECORD_BUTTON_CLICKED: 'CREATE_RECORD_BUTTON_CLICKED',
        RECORD_CREATED: 'RECORD_CREATED',
        DATATABLE_SEARCH_KEY_UP: 'DATATABLE_SEARCH_KEY_UP',
        EDIT_COLUMNS_BUTTON_CLICKED: 'EDIT_COLUMNS_BUTTON_CLICKED',
        COLUMN_SEARCH_KEY_UP: 'COLUMN_SEARCH_KEY_UP',
        COLUMNS_UPDATED: 'COLUMNS_UPDATED',
        EDIT_CUSTOM_OBJECT_BUTTON_CLICKED: 'EDIT_CUSTOM_OBJECT_BUTTON_CLICKED',
        CUSTOM_OBJECT_EDITED: 'CUSTOM_OBJECT_EDITED',
        CUSTOM_OBJECT_SEARCH_KEY_UP: 'CUSTOM_OBJECT_SEARCH_KEY_UP',
        DELETE_CUSTOM_OBJECT_BUTTON_CLICKED: 'DELETE_CUSTOM_OBJECT_BUTTON_CLICKED',
        CUSTOM_OBJECT_DELETED: 'CUSTOM_OBJECT_DELETED',
        EDIT_PROPERTY_GROUP_BUTTON_CLICKED: 'EDIT_PROPERTY_GROUP_BUTTON_CLICKED',
        PROPERTY_GROUP_EDITED: 'PROPERTY_GROUP_EDITED',
        DELETE_PROPERTY_GROUP_BUTTON_CLICKED: 'DELETE_PROPERTY_GROUP_BUTTON_CLICKED',
        PROPERTY_GROUP_DELETED: 'PROPERTY_GROUP_DELETED',
        DELETE_PROPERTY_BUTTON_CLICKED: 'DELETE_PROPERTY_BUTTON_CLICKED',
        PROPERTY_DELETED: 'PROPERTY_DELETED',
        PROPERTY_EDITED: 'PROPERTY_EDITED',
        PROPERTY_OR_VALUE_TOP_BAR_SEARCH_KEY_UP: 'PROPERTY_OR_VALUE_TOP_BAR_SEARCH_KEY_UP',
        EDIT_DEFAULT_PROPERTIES_BUTTON_CLICKED: 'EDIT_DEFAULT_PROPERTIES_BUTTON_CLICKED',
        PROPERTY_SEARCH_KEY_UP: 'PROPERTY_SEARCH_KEY_UP',
        DEFAULT_PROPERTIES_UPDATED: 'DEFAULT_PROPERTIES_UPDATED',
        ADD_FILTER_BUTTON_CLICKED: 'ADD_FILTER_BUTTON_CLICKED',
        CUSTOM_FILTER_ADDED: 'CUSTOM_FILTER_ADDED',
        APPLY_CUSTOM_FILTER_BUTTON_PRESSED: 'APPLY_CUSTOM_FILTER_BUTTON_PRESSED',
        CUSTOM_FILTER_REMOVED: 'CUSTOM_FILTER_REMOVED',
        FILTER_BACK_TO_HOME_BUTTON_CLICKED: 'FILTER_BACK_TO_HOME_BUTTON_CLICKED',
        FILTER_PROPERTY_LIST_ITEM_CLICKED: 'FILTER_PROPERTY_LIST_ITEM_CLICKED',
        FILTER_BACK_TO_LIST_BUTTON_CLICKED: 'FILTER_BACK_TO_LIST_BUTTON_CLICKED',
        EDIT_FILTER_BUTTON_CLICKED: 'EDIT_FILTER_BUTTON_CLICKED',
        FILTER_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED: 'FILTER_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED',
        FILTERS_UPDATED: 'FILTERS_UPDATED',
        FILTER_ALL_RECORDS_BUTTON_PRESSED: 'FILTER_ALL_RECORDS_BUTTON_PRESSED',
        ADVANCE_TO_REPORT_PROPERTIES_VIEW_BUTTON_CLICKED: 'ADVANCE_TO_REPORT_PROPERTIES_VIEW_BUTTON_CLICKED',
        ADVANCE_TO_REPORT_FILTERS_VIEW_BUTTON_CLICKED: 'ADVANCE_TO_REPORT_FILTERS_VIEW_BUTTON_CLICKED',
        BACK_TO_SELECT_CUSTOM_OBJECT_FOR_REPORT_BUTTON_PRESSED: 'BACK_TO_SELECT_CUSTOM_OBJECT_FOR_REPORT_BUTTON_PRESSED',
        REPORT_PROPERTY_LIST_ITEM_CLICKED: 'REPORT_PROPERTY_LIST_ITEM_CLICKED',
        REPORT_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED: 'REPORT_CUSTOM_OBJECT_PROPERTY_LIST_ITEM_CLICKED',
        REPORT_PROPERTY_LIST_ITEM_ADDED: 'REPORT_PROPERTY_LIST_ITEM_ADDED',
        REPORT_BACK_BUTTON_CLICKED: 'REPORT_BACK_BUTTON_CLICKED',
        REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED: 'REPORT_REMOVE_SELECTED_COLUMN_ICON_CLICKED',
        REPORT_PROPERTY_LIST_ITEM_REMOVED: 'REPORT_PROPERTY_LIST_ITEM_REMOVED',
        REPORT_FILTER_ITEM_CLICKED: 'REPORT_FILTER_ITEM_CLICKED',
        REPORT_FILTER_ITEM_ADDED: 'REPORT_FILTER_ITEM_ADDED',
        REPORT_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED: 'REPORT_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED',
        REPORT_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED: 'REPORT_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED',
        REPORT_CUSTOM_OBJECT_JOIN_PATH_SET: 'REPORT_CUSTOM_OBJECT_JOIN_PATH_SET',
        REPORT_ADD_FILTER_BUTTON_PRESSED: 'REPORT_ADD_FILTER_BUTTON_PRESSED',
        REPORT_ADD_OR_FILTER_BUTTON_PRESSED: 'REPORT_ADD_OR_FILTER_BUTTON_PRESSED',
        REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET: 'REPORT_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET',
        REPORT_REMOVE_FILTER_BUTTON_PRESSED: 'REPORT_REMOVE_FILTER_BUTTON_PRESSED',
        REPORT_FILTER_ITEM_REMOVED: 'REPORT_FILTER_ITEM_REMOVED',
        FILTER_BACK_TO_NAVIGATION_BUTTON_CLICKED: 'FILTER_BACK_TO_NAVIGATION_BUTTON_CLICKED',
        REPORT_BACK_TO_PROPERTIES_BUTTON_PRESSED: 'REPORT_BACK_TO_PROPERTIES_BUTTON_PRESSED',
        REPORT_EDIT_FILTER_BUTTON_CLICKED: 'REPORT_EDIT_FILTER_BUTTON_CLICKED',
        REPORT_SAVE_BUTTON_PRESSED: 'REPORT_SAVE_BUTTON_PRESSED',
        REPORT_NAME_CHANGED: 'REPORT_NAME_CHANGED',
        REPORT_SEARCH_KEY_UP: 'REPORT_SEARCH_KEY_UP',
        DELETE_REPORT_BUTTON_CLICKED: 'DELETE_REPORT_BUTTON_CLICKED',
        REPORT_DELETED: 'REPORT_DELETED',
        REPORT_COLUMN_ORDER_CHANGED: 'REPORT_COLUMN_ORDER_CHANGED',
        REPORT_COLUMN_ORDER_UPDATED: 'REPORT_COLUMN_ORDER_UPDATED',
        REPORT_PREVIEW_RESULTS_BUTTON_CLICKED: 'REPORT_PREVIEW_RESULTS_BUTTON_CLICKED',
        REPORT_PREVIEW_RESULTS_LOADED: 'REPORT_PREVIEW_RESULTS_LOADED',
        CREATE_USER_BUTTON_CLICKED: 'CREATE_USER_BUTTON_CLICKED',
        USER_CREATED: 'USER_CREATED',
        ROLES_AND_PERMISSIONS_BUTTON_CLICKED: 'ROLES_AND_PERMISSIONS_BUTTON_CLICKED',
        EDIT_ROLE_BUTTON_CLICKED: 'EDIT_ROLE_BUTTON_CLICKED',
        USER_SEARCH_KEY_UP: 'USER_SEARCH_KEY_UP',
        EDIT_USER_BUTTON_CLICKED: 'EDIT_USER_BUTTON_CLICKED',
        USER_EDITED: 'USER_EDITED',
        DELETE_USER_BUTTON_CLICKED: 'DELETE_USER_BUTTON_CLICKED',
        USER_DELETED: 'USER_DELETED',
        USER_SETTINGS_TOP_BAR_SEARCH_KEY_UP: 'USER_SETTINGS_TOP_BAR_SEARCH_KEY_UP',
        INVALID_OR_EXPIRED_PASSWORD_RESET_REQUEST: 'INVALID_OR_EXPIRED_PASSWORD_RESET_REQUEST',
        PASSWORD_SUCCESSFULLY_RESET: 'PASSWORD_SUCCESSFULLY_RESET',
        ROLE_CREATED: 'ROLE_CREATED',
        ROLE_EDITED: 'ROLE_EDITED',
        JOIN_FIELD_PROPERTY_LIST_ITEM_CLICKED: 'JOIN_FIELD_PROPERTY_LIST_ITEM_CLICKED',
        USER_FILTERS_UPDATED: 'USER_FILTERS_UPDATED',
        CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED: 'CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED',
        FILTER_CUSTOM_OBJECT_JOIN_PATH_SET: 'FILTER_CUSTOM_OBJECT_JOIN_PATH_SET',
        RESET_FILTERS_BUTTON_PRESSED: 'RESET_FILTERS_BUTTON_PRESSED',
        SAVED_FILTER_SEARCH_KEY_UP: 'SAVED_FILTER_SEARCH_KEY_UP',
        APPLY_SAVED_FILTER_BUTTON_CLICKED: 'APPLY_SAVED_FILTER_BUTTON_CLICKED',
        CREATE_LIST_BUTTON_CLICKED: 'CREATE_LIST_BUTTON_CLICKED',
        ADVANCE_TO_LIST_SELECT_CUSTOM_OBJECT_VIEW_BUTTON_CLICKED: 'ADVANCE_TO_LIST_SELECT_CUSTOM_OBJECT_VIEW_BUTTON_CLICKED',
        LIST_BACK_TO_SELECT_LIST_TYPE_BUTTON_CLICKED: 'LIST_BACK_TO_SELECT_LIST_TYPE_BUTTON_CLICKED',
        ADVANCE_TO_LIST_PROPERTIES_VIEW_BUTTON_CLICKED: 'ADVANCE_TO_LIST_PROPERTIES_VIEW_BUTTON_CLICKED',
        LIST_PROPERTY_LIST_ITEM_CLICKED: 'LIST_PROPERTY_LIST_ITEM_CLICKED',
        LIST_PROPERTY_LIST_ITEM_ADDED: 'LIST_PROPERTY_LIST_ITEM_ADDED',
        LIST_BACK_BUTTON_CLICKED: 'LIST_BACK_BUTTON_CLICKED',
        LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED: 'LIST_BACK_TO_SELECT_CUSTOM_OBJECT_BUTTON_PRESSED',
        LIST_ADVANCE_TO_FILTERS_VIEW_BUTTON_CLICKED: 'LIST_ADVANCE_TO_FILTERS_VIEW_BUTTON_CLICKED',
        LIST_ADD_FILTER_BUTTON_PRESSED: 'LIST_ADD_FILTER_BUTTON_PRESSED',
        LIST_FILTER_ITEM_CLICKED: 'LIST_FILTER_ITEM_CLICKED',
        LIST_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED: 'LIST_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED',
        LIST_FILTER_ITEM_ADDED: 'LIST_FILTER_ITEM_ADDED',
        LIST_REMOVE_FILTER_BUTTON_PRESSED: 'LIST_REMOVE_FILTER_BUTTON_PRESSED',
        LIST_FILTER_ITEM_REMOVED: 'LIST_FILTER_ITEM_REMOVED',
        LIST_ADD_OR_FILTER_BUTTON_PRESSED: 'LIST_ADD_OR_FILTER_BUTTON_PRESSED',
        LIST_EDIT_FILTER_BUTTON_CLICKED: 'LIST_EDIT_FILTER_BUTTON_CLICKED',
        LIST_BACK_TO_PROPERTIES_BUTTON_PRESSED: 'LIST_BACK_TO_PROPERTIES_BUTTON_PRESSED',
        LIST_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET: 'LIST_FILTER_CUSTOM_OBJECT_JOIN_PATH_SET',
        LIST_PREVIEW_RESULTS_BUTTON_CLICKED: 'LIST_PREVIEW_RESULTS_BUTTON_CLICKED',
        LIST_NAME_CHANGED: 'LIST_NAME_CHANGED',
        LIST_REMOVE_SELECTED_COLUMN_ICON_CLICKED: 'LIST_REMOVE_SELECTED_COLUMN_ICON_CLICKED',
        LIST_PROPERTY_LIST_ITEM_REMOVED: 'LIST_PROPERTY_LIST_ITEM_REMOVED',
        LIST_COLUMN_ORDER_CHANGED: 'LIST_COLUMN_ORDER_CHANGED',
        LIST_COLUMN_ORDER_UPDATED: 'LIST_COLUMN_ORDER_UPDATED',
        BULK_EDIT_SUCCESSFUL: 'BULK_EDIT_SUCCESSFUL',
        LIST_SAVE_BUTTON_PRESSED: 'LIST_SAVE_BUTTON_PRESSED',
        LIST_SEARCH_KEY_UP: 'LIST_SEARCH_KEY_UP',
        DELETE_LIST_BUTTON_CLICKED: 'DELETE_LIST_BUTTON_CLICKED',
        LIST_DELETED: 'LIST_DELETED',
        FOLDERS_BUTTON_CLICKED: 'FOLDERS_BUTTON_CLICKED',
        CREATE_FOLDER_BUTTON_CLICKED: 'CREATE_FOLDER_BUTTON_CLICKED',
        FOLDER_CREATED: 'FOLDER_CREATED',
        LIST_MOVED_TO_FOLDER: 'LIST_MOVED_TO_FOLDER',
        FOLDER_MODIFIED: 'FOLDER_MODIFIED',
        FOLDER_DELETED: 'FOLDER_DELETED',
        FORM_EDITOR_PROPERTY_LIST_ITEM_CLICKED: 'FORM_PROPERTY_LIST_ITEM_CLICKED',
        FORM_EDITOR_PROPERTY_LIST_ITEM_ADDED: 'FORM_PROPERTY_LIST_ITEM_ADDED',
        FORM_EDITOR_DATA_SAVED: 'FORM_DATA_SAVED',
        FORM_PREVIEW_DELETE_BUTTON_CLICKED: 'FORM_PREVIEW_DELETE_BUTTON_CLICKED',
        FORM_EDITOR_PROPERTY_LIST_ITEM_REMOVED: 'FORM_EDITOR_PROPERTY_LIST_ITEM_REMOVED',
        FORM_EDITOR_FIELD_ORDER_CHANGED: 'FORM_EDITOR_FIELD_ORDER_CHANGED',
        FORM_PREVIEW_EDIT_BUTTON_CLICKED: 'FORM_PREVIEW_EDIT_BUTTON_CLICKED',
        FORM_EDITOR_EDIT_FIELD_FORM_CHANGED: 'FORM_EDITOR_EDIT_FIELD_FORM_CHANGED',
        FORM_EDITOR_BACK_TO_LIST_BUTTON_CLICKED: 'FORM_EDITOR_BACK_TO_LIST_BUTTON_CLICKED',
        FORM_EDITOR_FORM_NAME_CHANGED: 'FORM_EDITOR_FORM_NAME_CHANGED',
        FORM_EDITOR_PUBLISH_FORM_BUTTON_CLICKED: 'FORM_EDITOR_PUBLISH_FORM_BUTTON_CLICKED',
        FORM_PUBLISHED: 'FORM_PUBLISHED',
        FORM_EDITOR_REVERT_BUTTON_CLICKED: 'FORM_EDITOR_REVERT_BUTTON_CLICKED',
        DELETE_FORM_BUTTON_CLICKED: 'DELETE_FORM_BUTTON_CLICKED',
        FORM_DELETED: 'FORM_DELETED',
        FORM_SEARCH_KEY_UP: 'FORM_SEARCH_KEY_UP',
        WORKFLOW_TRIGGER_LIST_ITEM_CLICKED: 'WORKFLOW_TRIGGER_LIST_ITEM_CLICKED',
        WORKFLOW_TRIGGER_CUSTOM_OBJECT_LIST_ITEM_CLICKED: 'WORKFLOW_TRIGGER_CUSTOM_OBJECT_LIST_ITEM_CLICKED',
        WORKFLOW_TRIGGER_PROPERTY_LIST_ITEM_CLICKED: 'WORKFLOW_TRIGGER_PROPERTY_LIST_ITEM_CLICKED',
        WORKFLOW_TRIGGER_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED: 'WORKFLOW_TRIGGER_CUSTOM_OBJECT_FILTER_LIST_ITEM_CLICKED',
        WORKFLOW_TRIGGER_ADD_OR_FILTER_BUTTON_PRESSED: 'WORKFLOW_TRIGGER_ADD_OR_FILTER_BUTTON_PRESSED',
        WORKFLOW_TRIGGER_ADD_FILTER_BUTTON_PRESSED: 'WORKFLOW_TRIGGER_ADD_FILTER_BUTTON_PRESSED',
        WORKFLOW_TRIGGER_ADDED: 'WORKFLOW_TRIGGER_ADDED',
        WORKFLOW_BACK_BUTTON_CLICKED: 'WORKFLOW_BACK_BUTTON_CLICKED',
        WORKFLOW_EDIT_TRIGGER_CLICKED: 'WORKFLOW_EDIT_TRIGGER_CLICKED',
        WORKFLOW_REMOVE_FILTER_BUTTON_PRESSED: 'WORKFLOW_REMOVE_FILTER_BUTTON_PRESSED',
        WORKFLOW_TRIGGER_FILTER_REMOVED: 'WORKFLOW_TRIGGER_FILTER_REMOVED',
        WORKFLOW_REMOVE_TRIGGER_BUTTON_PRESSED: 'WORKFLOW_REMOVE_TRIGGER_BUTTON_PRESSED',
        WORKFLOW_TRIGGER_REMOVED: 'WORKFLOW_TRIGGER_REMOVED',
        WORKFLOW_TRIGGER_FILTER_ADDED: 'WORKFLOW_TRIGGER_FILTER_ADDED',
        WORKFLOW_EDIT_FILTER_CLICKED: 'WORKFLOW_EDIT_FILTER_CLICKED',
        WORKFLOW_NEW_TRIGGER_BUTTON_PRESSED: 'WORKFLOW_NEW_TRIGGER_BUTTON_PRESSED',
        WORKFLOW_CUSTOM_OBJECT_SET: 'WORKFLOW_CUSTOM_OBJECT_SET',
        WORKFLOW_PUBLISH_BUTTON_CLICKED: 'WORKFLOW_PUBLISH_BUTTON_CLICKED',
        WORKFLOW_NAME_CHANGED: 'WORKFLOW_NAME_CHANGED',
        WORKFLOW_ADD_ACTION_BUTTON_PRESSED: 'WORKFLOW_ADD_ACTION_BUTTON_PRESSED',
        WORKFLOW_ACTION_LIST_ITEM_CLICKED: 'WORKFLOW_ACTION_LIST_ITEM_CLICKED',
        WORKFLOW_ACTION_PROPERTY_LIST_ITEM_CLICKED: 'WORKFLOW_ACTION_PROPERTY_LIST_ITEM_CLICKED',
        WORKFLOW_ACTION_CUSTOM_OBJECT_LIST_ITEM_CLICKED: 'WORKFLOW_ACTION_CUSTOM_OBJECT_LIST_ITEM_CLICKED',
        APPLY_WORKFLOW_ACTION_BUTTON_PRESSED: 'APPLY_WORKFLOW_ACTION_BUTTON_PRESSED',
        WORKFLOW_REMOVE_ACTION_BUTTON_PRESSED: 'WORKFLOW_REMOVE_ACTION_BUTTON_PRESSED',
        WORKFLOW_EDIT_ACTION_CLICKED: 'WORKFLOW_EDIT_ACTION_CLICKED',
        WORKFLOW_DATA_UPDATED: 'WORKFLOW_DATA_UPDATED',
        WORKFLOW_REVERT_BUTTON_CLICKED: 'WORKFLOW_REVERT_BUTTON_CLICKED',
        WORKFLOW_START_PAUSE_BUTTON_CLICKED: 'WORKFLOW_START_PAUSE_BUTTON_CLICKED',
        WORKFLOW_SEND_EMAIL_ACTION_FORM_SUBMIT: 'WORKFLOW_SEND_EMAIL_ACTION_FORM_SUBMIT'

    },

    PAGES: {
        FORM_EDITOR_EDIT_FORM: 'FORM_EDITOR_EDIT_FORM',
        FORM_EDITOR_EDIT_OPTIONS: 'FORM_EDITOR_EDIT_OPTIONS',
        WORKFLOW_TRIGGERS: 'WORKFLOW_TRIGGERS',
        WORKFLOW_ACTIONS: 'WORKFLOW_ACTIONS'
    },

    VIEWS: {
        WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE: 'WORKFLOW_TRIGGER_SELECT_TRIGGER_TYPE',
        WORKFLOW_TRIGGER_SELECT_CUSTOM_OBJECT: 'WORKFLOW_TRIGGER_SELECT_CUSTOM_OBJECT',
        WORKFLOW_ACTION_SELECT_TYPE: 'WORKFLOW_ACTION_SELECT_TYPE',
        WORKFLOW_ACTION_SELECT_PROPERTY: 'WORKFLOW_ACTION_SELECT_PROPERTY'
    }

};

export default Settings;