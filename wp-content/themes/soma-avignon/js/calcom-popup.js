/**
 * SOMA Avignon — Cal.com Popup
 * Ouvre un popup Cal.com pour la prise de RDV
 */
(function() {
    // Attendre que le DOM soit prêt
    function init() {
        var calcomURL = (typeof somaSettings !== 'undefined' && somaSettings.calcomUrl)
            ? somaSettings.calcomUrl + "?embed=true&theme=light"
            : null;

        if (!calcomURL) {
            console.warn('[SOMA] somaSettings.calcomUrl non défini');
            return;
        }

        // Créer le popup
        var popup = document.createElement('div');
        popup.id = 'calcom-popup';
        popup.innerHTML =
            '<div class="popup-overlay"></div>' +
            '<div class="popup-content">' +
                '<button class="popup-close" aria-label="Fermer">&times;</button>' +
                '<h2>Prenez rendez-vous</h2>' +
                '<iframe width="100%" height="700" frameborder="0" style="border:none;" title="Réservation Cal.com"></iframe>' +
            '</div>';
        document.body.appendChild(popup);

        var iframeLoaded = false;

        // Ouvrir le popup
        function openPopup(e) {
            if (e) e.preventDefault();

            // Charger l'iframe au premier clic
            if (!iframeLoaded) {
                var iframe = popup.querySelector('iframe');
                iframe.src = calcomURL;
                iframeLoaded = true;
            }

            popup.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Fermer le popup
        function closePopup() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Délégation d'événements sur tout le document
        document.addEventListener('click', function(e) {
            var el = e.target.closest('a, button');
            if (!el) return;

            // 1. Lien menu CTA (#rdv)
            var href = el.getAttribute('href');
            if (href === '#rdv' || href === '#RDV') {
                openPopup(e);
                return;
            }

            // 2. Classe .soma-rdv-btn
            if (el.classList.contains('soma-rdv-btn')) {
                openPopup(e);
                return;
            }

            // 3. Bouton avec texte rendez-vous / réserver
            if (el.classList.contains('wp-block-button__link') || el.classList.contains('btn-soma')) {
                var text = el.textContent.trim().toLowerCase();
                if (text.indexOf('rendez-vous') !== -1 ||
                    text.indexOf('réserver') !== -1) {
                    openPopup(e);
                    return;
                }
            }

            // 4. Fermer le popup
            if (el.classList.contains('popup-close')) {
                closePopup();
                return;
            }
        });

        // Fermer en cliquant sur l'overlay
        popup.querySelector('.popup-overlay').addEventListener('click', closePopup);

        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });
    }

    // Lancer quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
