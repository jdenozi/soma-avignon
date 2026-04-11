# SOMA Avignon — Guide d'installation (v2.0)

## Prérequis
- WordPress 6.0+
- PHP 7.4+
- Thème parent **Astra** installé (gratuit)

## Installation pas à pas

### 1. Installer Astra (thème parent)
- WordPress Admin → **Apparence → Thèmes → Ajouter**
- Chercher **"Astra"** → Installer → NE PAS activer

### 2. Installer le child theme SOMA Avignon
- Copier le dossier `soma-avignon/` dans `/wp-content/themes/`
- WordPress Admin → **Apparence → Thèmes** → Activer **SOMA Avignon**

### 3. Plugins recommandés
| Plugin | Usage |
|--------|-------|
| **Contact Form 7** | Formulaire de contact |
| **Smash Balloon Instagram Feed** | Feed Instagram (optionnel) |

### 4. Configurer le Customizer
WordPress Admin → **Apparence → Personnaliser** :

- **SOMA — Informations** : téléphone, email, adresse, horaires
- **SOMA — Réseaux sociaux** : URL Instagram, Facebook
- **SOMA — Réservation & Paiement** : URL Cal.com, lien Stripe
- **SOMA — Pied de page** : texte footer, iframe Google Maps
- **Identité du site** : logo, titre, favicon

### 5. Créer les pages
Créer ces 4 pages dans WordPress (Pages → Ajouter) :

| Page | Slug | Contenu |
|------|------|---------|
| Accueil | `accueil` | Copier `page-templates/accueil.html` (mode Code) |
| Prestations | `prestations` | Copier `page-templates/prestations.html` |
| Collaboration Atelier SOMA | `collaboration-atelier-soma` | Copier `page-templates/collaboration.html` |
| Contact | `contact` | Copier `page-templates/contact.html` |

> **Astuce** : Dans l'éditeur, cliquer sur les 3 points (⋮) → **Éditeur de code** pour coller le HTML.

### 6. Définir la page d'accueil
- WordPress Admin → **Réglages → Lecture**
- "Votre page d'accueil affiche" → **Une page statique**
- Page d'accueil → **Accueil**

### 7. Créer le menu
- WordPress Admin → **Apparence → Menus**
- Créer un menu "Menu principal"
- Ajouter les pages : Accueil, Prestations, Collaboration, Contact
- Ajouter un élément personnalisé "Prendre RDV" avec URL `#rdv`
  → Lui ajouter la classe CSS `menu-cta nav-cta` (activer les classes CSS dans Options de l'écran)
- Assigner à l'emplacement **Menu principal**

### 8. Ajouter les prestations
WordPress Admin → **Prestations → Ajouter** :

Exemples de prestations à créer :
- **Massage énergétique crânien** — 60 min — 65€
- **Massage prénatal** — 60 min — 65€
- **Massage postnatal** — 60 min — 65€

Pour chaque prestation : titre, description, image mise en avant, prix et durée (dans le panneau latéral).

### 9. Ajouter les témoignages
WordPress Admin → **Témoignages → Ajouter** :
- Titre = Nom du client (ex: "Marie L.")
- Contenu = Le texte du témoignage
- Panneau latéral = Nombre d'étoiles

### 10. Configurer Cal.com
Dans le Customizer → **SOMA — Réservation & Paiement** :
- Entrer votre URL Cal.com (ex: `https://calcom.tempo-hub.fr/votre-nom`)
- Les boutons "Prendre rendez-vous" / "Réserver" ouvriront automatiquement le popup Cal.com

### 11. Configurer Stripe
- Créer un lien de paiement sur [dashboard.stripe.com](https://dashboard.stripe.com)
- Coller le lien dans le Customizer → **SOMA — Réservation & Paiement** → Lien Stripe
- Mettre à jour le href du bouton "Payer en ligne" dans les pages

## Shortcodes disponibles

| Shortcode | Description |
|-----------|-------------|
| `[soma_prestations limit="6"]` | Grille de cartes prestations |
| `[soma_temoignages limit="3"]` | Grille de témoignages |
| `[soma_contact_info]` | Bloc infos de contact avec icônes + carte |
| `[soma_social_links]` | Icônes réseaux sociaux |
| `[soma_stripe_button text="Payer"]` | Bouton de paiement Stripe |
| `[soma_rdv_button text="Réserver"]` | Bouton popup Cal.com |
| `[soma_stats items="500+\|Label,..."]` | Compteurs animés (séparés par virgule, nombre\|label) |
| `[soma_marquee items="Mot 1,Mot 2,..."]` | Bandeau défilant horizontal |
| `[soma_floating_cta text="RDV"]` | Bouton flottant fixe (ajouté auto sur toutes les pages) |

## Classes CSS utiles pour l'éditeur

| Classe | Effet |
|--------|-------|
| `soma-section` | Padding section (6rem) |
| `soma-section-beige` | Fond beige |
| `soma-section-white` | Fond blanc |
| `soma-section-terracotta` | Fond terracotta dégradé |
| `soma-subtitle` | Style sous-titre (majuscules, terracotta) |
| `soma-divider` | Petit trait décoratif camel |
| `soma-fade-in` | Animation apparition au scroll (bas → haut) |
| `soma-fade-in-left` | Animation apparition (gauche → droite) |
| `soma-fade-in-right` | Animation apparition (droite → gauche) |
| `soma-scale-in` | Animation zoom au scroll |
| `soma-stagger` | Animation enfants décalée |
| `soma-page-header` | En-tête de page avec fond beige |
| `soma-about-img` | Image avec cadre décoratif camel |
| `soma-big-quote` | Citation mise en valeur (Cormorant Garamond) |
| `soma-values` | Colonnes valeurs avec hover |
| `soma-highlight` | Texte mis en valeur terracotta |

## Fonctionnalités v2.0

- **Parallax** : effet de parallaxe subtil sur le hero (desktop)
- **Compteurs animés** : les chiffres s'animent au scroll
- **Bandeau défilant** : marquee horizontal avec mots-clés
- **Bouton flottant** : CTA fixe qui apparaît au scroll
- **Scroll-to-top** : bouton retour en haut de page
- **Micro-interactions** : shine effect sur boutons, hover améliorés
- **Cadre décoratif** : bordure camel autour des images `.soma-about-img`
- **Icônes SVG** : contact info avec icônes intégrées

## Paiement — WP Simple Pay Pro + Stripe Connect

### Architecture
- **Toi** (Julien) = la plateforme. Tu as ton propre compte Stripe.
- **Chaque client** (ex: SOMA Avignon) = un "connected account" Stripe.
- Sur chaque paiement, tu prélèves automatiquement ta commission (application fee).

### Mise en place
1. **Acheter WP Simple Pay Pro** — plan "Professional" ($199/an) sur wpsimplepay.com
2. **Uploader le plugin** dans WordPress (Extensions → Ajouter → Téléverser)
   - Il remplacera la version gratuite automatiquement
3. **Activer Stripe Connect** dans WP Simple Pay → Settings → Stripe → Connect with Stripe
4. **Demander au client** de connecter son compte Stripe via le bouton "Connect"
5. **Configurer l'application fee** (ta commission) dans les settings du formulaire

### Formulaire de paiement
- Créer un formulaire dans WP Simple Pay → Add New
- Ajouter les prestations comme "Price options"
- L'ID du formulaire s'insère dans le Customizer (SOMA — Réservation & Paiement)
- Le shortcode `[soma_payment]` l'affichera automatiquement sur la page /paiement/

### Pages déjà créées
| Page | Slug | Rôle |
|------|------|------|
| Paiement | `/paiement/` | Formulaire de paiement |
| Confirmation de paiement | `/confirmation-paiement/` | Après paiement réussi |
| Paiement échoué | `/paiement-echoue/` | Après échec |

## Palette de couleurs

| Couleur | Hex | Usage |
|---------|-----|-------|
| Beige clair | `#FAF7F4` | Fond principal |
| Beige | `#F5F0EB` | Sections alternées |
| Camel | `#C4A882` | Accents, décorations |
| Terracotta | `#C47A5A` | Boutons, liens, CTAs |
| Or | `#B8964E` | Étoiles, bouton Stripe |
| Texte | `#3D3029` | Texte principal |
