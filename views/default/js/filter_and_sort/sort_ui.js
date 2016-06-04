define(function(require)
{
    var $ = require('jquery');
    var elgg = require('elgg');

    $.extend({
      getUrlVars: function(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

        for(var i = 0; i < hashes.length; i++)
        {
          hash = hashes[i].split('=');
          vars.push(hash[0]);
          vars[hash[0]] = hash[1];
        }
        return vars;
      },
      getUrlVar: function(name){
        return $.getUrlVars()[name];
      }
    });

    getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    // grab currently selected filter options from UI, build the URL for the options and then forward the browser to the new URL
    changeFilter = function(status)
    {
        var timing_to_val;
        var timing_from_val;
        var url = window.location.href;
        if (window.location.search.length) {
            url = url.substring(0, url.indexOf('?'));
        }
        var filter_param = getUrlParameter('filter');
        var sort_val = $("#sort").val();

        // use default date values (all) if the date fields match their original defaults
        if ($('#timing-from').data('default_from') == $("input[name='timing-from']").val())
          timing_from_val = 'all';
        else
          timing_from_val = $("input[name='timing-from']").val();

        if ($('#timing-to').data('default_to') == $("input[name='timing-to']").val())
          timing_to_val = 'all';
        else
          timing_to_val = $("input[name='timing-to']").val();

        // if the reset button was clicked then use default date values
        if (status == 'reset')
        {
          timing_from_val = 'all';
          timing_to_val = 'all';
        }

        var contain_val = $("#contain").val();
        var status_val = $("#status").val();
        var objtype_val = $("#elgg-river-selector").val();
        if (($('#icon').length > 0) &&(document.getElementById('icon').checked))
            var icon_val = 'true';
        else
            var icon_val = 'false';

        if ($('#status').length == 0)
            status_val = 'all';
        var limit_val = $("#limit").val();
        var list_type = $.getUrlVar("list_type");
        if ((list_type != 'gallery')&&(list_type != 'list'))
                list_type = 'list';

        url += '?sort=' + sort_val + '&' + 'timing_from=' + timing_from_val + '&' + 'timing_to=' + timing_to_val + '&' + 'contain=' + contain_val + '&' + 'status=' + status_val + '&' + 'limit=' + limit_val + '&' + 'list_type=' + list_type;
        if (icon_val)
                    url += '&' + 'show_icon=' + icon_val;
        if (filter_param)
          url += '&' + 'filter=' + filter_param;
        if (objtype_val)
          url += '&' + objtype_val;
        elgg.forward(url);
    };

    init = function()
    {
        $('#filter_options_go_btn').click(function() {
            // if the to-date is before the from-date then throw an error
            if (parseInt($("input[name='timing-to']").val()) < parseInt($("input[name='timing-from']").val()))
              elgg.register_error(elgg.echo('filter_and_sort:datepicker_mismatch'));
            // otherwise, process the filter state and reload the page
            else
              changeFilter();
        });

        // if url params exist or cookie present
        if (((window.location.href.indexOf('?') != -1))||($('#elgg-sort-options').data('cookie') == 1))
        {
            var params = $.getUrlVars();
            var firstparam = 0;
            if (params[0] == '')
                firstparam = 1;

            if ($('#icon').length > 0)
                var icon_check = document.getElementById('icon').checked;

            // if any filters are active
            if (($(".elgg-active-sort-filter").length > 0)||(icon_check))
            {
                // reveal filter control area
                $("#elgg-filter-options").slideDown( "slow" );
                $('#elgg-filter-options-link').html(elgg.echo('filter_and_sort:hide'));
            }
        }

        $( "#elgg-filter-options-link").click(function () {
            if ( $("#elgg-filter-options").is( ":hidden" ) ) {
                $("#elgg-filter-options").slideDown( "slow" );
                $('#elgg-filter-options-link').html(elgg.echo('filter_and_sort:hide'));
            } else {
                $('#elgg-filter-options-link').html(elgg.echo('filter_and_sort:show'));
                $("#elgg-filter-options").slideUp( "fast" );
            }
        });

        $("#elgg-reset-sort-filters").click(function () {
            // reset dropdowns to default options
            $("#elgg-filter-options select").prop('selectedIndex', 0);
            // set date fields to default
            changeFilter('reset');
        });

        $( ".elgg-active-sort-filter").click(function () {
            var filter = $(this).attr('data');
            if (filter == 'objtype')
              filter = 'elgg-river-selector';
            if (filter == 'timing-to')
              var reset = 'reset';
            $("#" + filter).prop('selectedIndex', 0);
            changeFilter('reset');
        });
        $( ".elgg-active-sort-filter").mouseover(function () {
            $(this).children().css( "background-position", "0 -127px" );
        })
        .mouseout(function () {
            $(this).children().css( "background-position", "0 -136px" );
        });
    };

    elgg.register_hook_handler('init', 'system', init);
});
