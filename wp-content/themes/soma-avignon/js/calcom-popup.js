/**
 * SOMA Avignon — Cal.com Popup
 * Ouvre un popup Cal.com pour la prise de RDV
 */
jQuery(document).ready(function($) {
    // URL Cal.com récupérée depuis le Customizer WordPress
    var calcomURL = somaSettings.calcomUrl + "?embed=true&theme=light";

    // Crée le popup avec iframe Cal.com
    var popupHTML = '<div id="calcom-popup">' +
        '<div class="popup-overlay"></div>' +
        '<div class="popup-content">' +
            '<button class="popup-close" aria-label="Fermer">&times;</button>' +
            '<h2>Prenez un rendez-vous</h2>' +
            '<iframe src="" data-src="' + calcomURL + '" width="100%" height="700" frameborder="0" style="border:none;" title="Réservation Cal.com"></iframe>' +
        '</div>' +
    '</div>';

    $('body').append(popupHTML);

    // Ouvre le popup au clic sur les boutons de RDV
    function openPopup(e) {
        e.preventDefault();
        var $popup = $('#calcom-popup');
        var $iframe = $popup.find('iframe');

        // Lazy load l'iframe au premier clic
        if (!$iframe.attr('src') || $iframe.attr('src') === '') {
            $iframe.attr('src', $iframe.data('src'));
        }

        $popup.show();
        $('body').css('overflow', 'hidden');
    }

    // Boutons WordPress (Gutenberg)
    $(document).on('click', '.wp-block-button__link', function(e) {
        var text = $(this).text().trim();
        if (text.indexOf('Prendre un rendez-vous') !== -1 ||
            text.indexOf('Commencez') !== -1 ||
            text.indexOf('Réserver') !== -1 ||
            text.indexOf('rendez-vous') !== -1) {
            openPopup(e);
        }
    });

    // Boutons shortcode
    $(document).on('click', '.soma-rdv-btn', function(e) {
        openPopup(e);
    });

    // Ferme le popup
    $(document).on('click', '#calcom-popup .popup-close, #calcom-popup .popup-overlay', function() {
        $('#calcom-popup').hide();
        $('body').css('overflow', '');
    });

    // Ferme avec Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#calcom-popup').hide();
            $('body').css('overflow', '');
        }
    });
});
