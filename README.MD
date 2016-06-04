=============================
PLUGIN NAME
=============================
Filter & sort - for elgg 1.12.x

=============================
PLUGIN DESCRIPTION
=============================

adds sorting and filtering functionality to elgg entity lists.

features:

* bi-directional sort modes: 'alphabetical', 'created data', 'updated date', 'most likes', 'most comments', 'most views'.
* timing period filter: allows content to be shown only for the time period chosen in the 'to' and 'from' datepickers (applies to 'created date').
* container filter: list can be filtered by specific groups, all groups, by user's profile or not at all.
* size limit: allows user to set page size
* active filter buttons: allows individual filter options to be seen easily and cleared with a click.
* auto-hiding filter options area: is revealed when filters are in use and hidden when not.
* supports group lists.
* supports activity lists, including the keyword tab for tag_tools.
* the many plugin pages are replaced with a small set of four page templates - making uniform editing of templates across an elgg site much simpler.
* removes breadcrumb from entity list pages (since the breadcrumb entry is only a duplicate of the page title below it and is not of any value for navigation).
* adds list / gallery toggle button to all lists except for tidypics.
* uses cookies to save the sort/filter state of entity lists. unique cookies are created for each site context.
* allows key parameters for hypeLists to be applied across all list pages via options in the admin panel

supports these plugins:

blog_tools
views counter
entity_view_counter
tidypics
videolist
polls
pinboards
core entity plugins (file, pages, blog etc.)
likes + etklikes
pagehandler_hijack
group_tools
river_addon
tag_tools

notes:

this plugin totally replaces the pages for the 'all', 'mine', 'friends' and 'featured' lists of each of the supported entity types.

the sidebar of tidypics is simplified down to only the basics, in place of the many options that were present in the beta versions of tidypics for elgg 1.8. mostly, the functions supported by the pages that are accessible via the sidebar in a standard tidypics installation are now supported via the drop-down interface that 'filter_and_sort' provides.

the 'list type' toggle button is now enabled by default for all list types, to allow gallery or list modes to be chosen.

to do:

* support for search results lists
* integrate in with supajax plugin to allow full ajaxed lists and sorting/filtering.

IF YOU VALUE THIS PLUGIN, CONSIDER MAKING A DONATION AT www.ureka.org/donate

=============================
CHANGELOG
=============================

0.1
initial release

0.2
added: support for group lists
added: support for river activity
added: support for tag_tools
added: datepickers
fixed: removed hjcategories from the subtype selector on activity page
