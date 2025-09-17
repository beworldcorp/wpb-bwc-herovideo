jQuery(function ($) {
  if (!window.WPbwc || !Array.isArray(WPbwc.videos_ids)) return;

  for (const id of WPbwc.videos_ids) {
    // Desktop
    $('#d-audio-control-' + id).on('click', function () {
      const $v = $('#desktop-' + id);
      if ($v.prop('muted')) {
        $v.prop('muted', false);
        $(this).text('volume_up');
      } else {
        $v.prop('muted', true);
        $(this).text('volume_off');
      }
    });

    // Mobile
    $('#m-audio-control-' + id).on('click', function () {
      const $v = $('#mobile-' + id);
      if ($v.prop('muted')) {
        $v.prop('muted', false);
        $(this).text('volume_up');
      } else {
        $v.prop('muted', true);
        $(this).text('volume_off');
      }
    });
  }
});