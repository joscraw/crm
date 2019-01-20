'use strict';

import $ from 'jquery';

/**
 * Nice little helper function that allows you to use a regular expression as a selector
 *
 * @see {@link https://j11y.io/javascript/regex-selector-for-jquery/}
 *
 * @param elem
 * @param index
 * @param match
 * @return {boolean}
 */
$.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
};

class FormCollectionPrototypeUpdater {

    /**
     * Determines the field prefix from one of the child items.
     * This is needed to update the prototype indexes
     *
     * @param $container
     */
    static getFieldPrefix($container) {

        var regexString = '^([a-zA-Z_]+_)[0-9]+_[A-Za-z_]+';
        var selector = ':regex(id,' + regexString + ')';
        var $element = $container.find(selector);
        var field = $element.first().attr('id');
        var regex = new RegExp(regexString,"g");

        return field.replace(regex, "$1");

    }

    static updateAttributes($container, fieldPrefix, currentIndex, replacementIndex) {

        var match,
            replacePattern,
            idSearchPattern   = new RegExp(fieldPrefix + '([a-zA-Z_]*)' + currentIndex),
            nameSearchPattern = new RegExp('\\[' + currentIndex + '\\]');

        replacementIndex = (undefined !== replacementIndex) ? replacementIndex : (currentIndex - 1);

        $container.find('[id*="' + fieldPrefix + '"], [for*="' + fieldPrefix + '"]').each(function (index, el) {

            if (undefined !== el.id && el.id.length > 0) {

                match          = idSearchPattern.exec(el.id);

                if(!match) {
                    return;
                }

                replacePattern = fieldPrefix + match[1] + currentIndex;
                el.id          = el.id.replace(replacePattern, fieldPrefix + match[1] + replacementIndex);
            }

            if (undefined !== el.name) {
                el.attributes.name.value = el.attributes.name.value.replace(nameSearchPattern, '[' + replacementIndex + ']');
            }

            if (undefined !== el.attributes.for) {

                match          = idSearchPattern.exec(el.attributes.for.value);

                if(!match) {
                    return;
                }

                replacePattern = fieldPrefix + match[1] + currentIndex;
                el.attributes.for.value = el.attributes.for.value.replace(replacePattern, fieldPrefix + match[1] + replacementIndex);
            }
        });
    }
}

export default FormCollectionPrototypeUpdater;