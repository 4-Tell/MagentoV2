/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    function processVariables(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html'
        }).done(function (data) {
            $('#recommend-variables-container').html(data);
        });
    }

    return function (config, element) {
        processVariables(config.productVariablesUrl);
    };
});
