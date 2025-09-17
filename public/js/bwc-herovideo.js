jQuery(function ($) {
  if (!window.WPbwc || !Array.isArray(WPbwc.videos_ids)) return;
  const FallbackDelay = parseInt(WPbwc.fallbackTimeoutMs, 10) || 4000;

  function bindVideoFallback(kind, id) {
    const $video = $('#' + kind + '-' + id);
    const $img   = $('#' + kind + '-fallback-' + id);
    if ($video.length === 0 || $img.length === 0) return;

    const video = $video.get(0);
    let hasPlayed = false;

    // Quand la vidéo joue → on masque le fallback et on note qu’elle marche
    video.addEventListener('playing', () => {
      hasPlayed = true;
      $img.hide();
      $video.show();
    }, { passive: true });

    // Après un délai, si jamais la vidéo n’a pas réussi à jouer → on montre l’image
    setTimeout(() => {
      if (!hasPlayed && video.networkState === 3) {
        $video.hide();
        $img.show();
      }
    }, FallbackDelay);

    // Si jamais la vidéo a une vraie erreur réseau → fallback
    video.addEventListener('error', () => {
      if (!hasPlayed) {
        $video.hide();
        $img.show();
      }
    }, { passive: true });
  }

  for (const id of WPbwc.videos_ids) {
    bindVideoFallback('desktop', id);
    bindVideoFallback('mobile', id);

    $('#d-audio-control-' + id).on('click', function () {
      const $v = $('#desktop-' + id);
      const muted = $v.prop('muted');
      $v.prop('muted', !muted);
      $(this).text(muted ? 'volume_up' : 'volume_off');
    });

    $('#m-audio-control-' + id).on('click', function () {
      const $v = $('#mobile-' + id);
      const muted = $v.prop('muted');
      $v.prop('muted', !muted);
      $(this).text(muted ? 'volume_up' : 'volume_off');
    });
  }
});
