jQuery(function ($) {
  if (!window.WPbwc || !Array.isArray(WPbwc.videos_ids)) return;
  const FallbackDelay = parseInt(WPbwc.fallbackTimeoutMs, 10) || 4000;

  function bindVideoFallback(kind, id) {
    const $video = $('#' + kind + '-' + id);
    const $img   = $('#' + kind + '-fallback-' + id);
    if ($video.length === 0 || $img.length === 0) return;

    const video = $video.get(0);
    let timer = setTimeout(() => {
      if (video.networkState === 3) { // NETWORK_NO_SOURCE
        $video.hide();
        $img.show();
      }
    }, FallbackDelay);

    const showFallback = () => {
      clearTimeout(timer);
      $video.hide();
      $img.show();
    };

    video.addEventListener('error',   showFallback, { passive: true });
    video.addEventListener('stalled', showFallback, { passive: true });
    video.addEventListener('abort',   showFallback, { passive: true });
    video.addEventListener('emptied', showFallback, { passive: true });

    video.addEventListener('playing', () => { clearTimeout(timer); }, { passive: true });
    video.addEventListener('canplay', () => { clearTimeout(timer); }, { passive: true });
  }

  for (const id of WPbwc.videos_ids) {
    // Fallback desktop/mobile
    bindVideoFallback('desktop', id);
    bindVideoFallback('mobile',  id);

    // Contrôle audio desktop
    $('#d-audio-control-' + id).on('click', function () {
      const $v = $('#desktop-' + id);
      const muted = $v.prop('muted');
      $v.prop('muted', !muted);
      $(this).text(muted ? 'volume_up' : 'volume_off');
    });

    // Contrôle audio mobile
    $('#m-audio-control-' + id).on('click', function () {
      const $v = $('#mobile-' + id);
      const muted = $v.prop('muted');
      $v.prop('muted', !muted);
      $(this).text(muted ? 'volume_up' : 'volume_off');
    });
  }
});
