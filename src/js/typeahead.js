/* globals Bloodhound, UCF_LOCATIONS_SEARCH */

const UCFLocationsSearch = function (args) {
  this.$object = args.selector ? args.selector : $('.locations-search');

  this.datumTokenizer = function (datum) {
    return Bloodhound.tokenizers.whitespace(datum.title.rendered);
  };

  this.queryTokenizer = function (q) {
    return Bloodhound.tokenizers.whitespace(q);
  };

  this.displayKey = function (location) {
    return jQuery('<span>').html(location.title.rendered).text();
  };

  this.engine = new Bloodhound({
    remote: {
      url: UCF_LOCATIONS_SEARCH.remote_path,
      wildcard: '%q'
    },
    datumTokenizer: this.datumTokenizer,
    queryTokenizer: this.queryTokenizer
  });

  this.$object.typeahead({
    minLength: 3,
    highlight: true
  },
  {
    name: 'locations-search-terms',
    limit: 5,
    displayKey: this.displayKey,
    source: this.engine.ttAdapter()
  }).on('typeahead:selected', (event, obj) => {
    window.location = obj.link;
  });
};

(function ($) {
  const $objects = $('.location-search');

  if ($objects.length > 0) {
    $objects.each(($x) => {
      UCFLocationsSearch({
        selector: jQuery($objects[$x])
      });
    });
  }
}(jQuery));
