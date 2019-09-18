const autoCollapse = function ($) {
  const $repeater = $('div[data-name="ucf_location_orgs"]');

  if ($repeater.length > 0) {
    const $rows = $repeater.find('.acf-row');

    if ($rows.length > 0) {
      $rows.addClass('-collapsed');
    }
  }
};

jQuery(document).ready(() => {
  autoCollapse($);
});
