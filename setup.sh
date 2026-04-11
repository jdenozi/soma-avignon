#!/usr/bin/env bash
set -uo pipefail

# ─── Detect docker compose command ───
if docker compose version > /dev/null 2>&1; then
  DC="docker compose"
elif docker-compose version > /dev/null 2>&1; then
  DC="docker-compose"
else
  echo "✗ Neither 'docker compose' nor 'docker-compose' found."
  exit 1
fi

echo "╔══════════════════════════════════════╗"
echo "║  SOMA Avignon — Setup               ║"
echo "╚══════════════════════════════════════╝"
echo ""

# ─── Install WP-CLI in container ───
echo "→ Installation de WP-CLI dans le container..."
$DC exec -T wordpress bash -c 'which wp > /dev/null 2>&1 || (curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp && echo "  WP-CLI installé")'

WP="$DC exec -T wordpress wp --allow-root"

# ─── Wait for WordPress to be ready ───
echo "→ Attente de WordPress..."
for i in $(seq 1 30); do
  if $WP core is-installed 2>/dev/null; then
    break
  fi
  sleep 2
done

if ! $WP core is-installed 2>/dev/null; then
  echo "→ WordPress pas encore installé, installation..."
  $WP core install \
    --url="http://localhost:8090" \
    --title="SOMA Avignon" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@soma-avignon.fr \
    --locale=fr_FR \
    --skip-email
fi

echo "→ Configuration de WordPress..."
$WP option update blogname "SOMA Avignon"
$WP option update blogdescription "Massage énergétique crânien & Coiffure à Avignon"
$WP option update timezone_string "Europe/Paris"
$WP option update date_format "j F Y"
$WP option update time_format "H\hi"
$WP language core install fr_FR --activate 2>/dev/null || true

# ─── Thème ───
echo "→ Activation du thème..."
$WP theme install astra --activate 2>/dev/null || true
$WP theme activate soma-avignon

# ─── Plugins ───
echo "→ Installation des plugins..."
$WP plugin install contact-form-7 --activate 2>/dev/null || true

# ─── Suppression contenu par défaut ───
echo "→ Nettoyage du contenu par défaut..."
$WP post delete 1 --force 2>/dev/null || true
$WP post delete 2 --force 2>/dev/null || true
$WP post delete 3 --force 2>/dev/null || true

# ─── Création des pages ───
echo "→ Création des pages..."

create_page() {
  local title="$1"
  local slug="$2"
  local template_file="$3"

  # Vérifie si la page existe déjà
  local existing=$($WP post list --post_type=page --name="$slug" --format=ids 2>/dev/null)
  if [ -n "$existing" ]; then
    echo "  Page '$title' existe déjà (ID: $existing), mise à jour..."
    $DC cp "wp-content/themes/soma-avignon/page-templates/$template_file" wordpress:/tmp/$template_file
    $DC exec -T wordpress bash -c "wp post update $existing --post_content=\"\$(cat /tmp/$template_file)\" --allow-root"
    $DC exec -T wordpress wp post meta update $existing _wp_page_template default --allow-root
  else
    $DC cp "wp-content/themes/soma-avignon/page-templates/$template_file" wordpress:/tmp/$template_file
    local page_id=$($DC exec -T wordpress bash -c "wp post create --post_type=page --post_title='$title' --post_name='$slug' --post_status=publish --porcelain --allow-root")
    $DC exec -T wordpress bash -c "wp post update $page_id --post_content=\"\$(cat /tmp/$template_file)\" --allow-root"
    $DC exec -T wordpress wp post meta update $page_id _wp_page_template default --allow-root
    echo "  Page '$title' créée (ID: $page_id)"
  fi
}

create_page "Accueil" "accueil" "accueil.html"
create_page "Prestations & Rituels" "prestations" "prestations.html"
create_page "Collaboration Atelier SOMA" "collaboration-atelier-soma" "collaboration.html"
create_page "Contact" "contact" "contact.html"

# ─── Page d'accueil statique ───
echo "→ Configuration de la page d'accueil..."
ACCUEIL_ID=$($WP post list --post_type=page --name=accueil --format=ids)
$WP option update show_on_front page
$WP option update page_on_front "$ACCUEIL_ID"

# ─── Permaliens ───
echo "→ Configuration des permaliens..."
$WP rewrite structure '/%postname%/'
$WP rewrite flush

# ─── Menu ───
echo "→ Création du menu..."
$WP menu delete "Menu Principal" 2>/dev/null || true
$WP menu create "Menu Principal"

ACCUEIL_ID=$($WP post list --post_type=page --name=accueil --format=ids)
PRESTA_ID=$($WP post list --post_type=page --name=prestations --format=ids)
COLLAB_ID=$($WP post list --post_type=page --name=collaboration-atelier-soma --format=ids)
CONTACT_ID=$($WP post list --post_type=page --name=contact --format=ids)

$WP menu item add-post "Menu Principal" "$ACCUEIL_ID" --title="Accueil" --position=1
$WP menu item add-post "Menu Principal" "$PRESTA_ID" --title="Prestations" --position=2
$WP menu item add-post "Menu Principal" "$COLLAB_ID" --title="Atelier SOMA" --position=3
$WP menu item add-post "Menu Principal" "$CONTACT_ID" --title="Contact" --position=4

# Bouton CTA RDV
RDV_ITEM_ID=$($WP menu item add-custom "Menu Principal" "Prendre RDV" "#rdv" --position=5 --porcelain)
$WP menu item update "$RDV_ITEM_ID" --classes="nav-cta" 2>/dev/null || true

$WP menu location assign "Menu Principal" primary

# ─── Theme Mods ───
echo "→ Configuration du thème..."
$WP theme mod set soma_calcom_url "https://calcom.tempo-hub.fr/laurie-couderchet"
$WP theme mod set soma_phone "06 50 85 21 67"
$WP theme mod set soma_email "contact@soma-avignon.fr"
$WP theme mod set soma_address "20 Boulevard Paul Mariéton, 84000 Avignon"
$WP theme mod set soma_hours "Lundi - Vendredi : 09h30 - 17h30"
$WP theme mod set soma_instagram "https://www.instagram.com/soma.avignon/"

$WP theme mod set soma_google_maps_embed '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d91926.61358798794!2d4.750390497522362!3d43.94474389588553!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x84f64115da1336dd%3A0x5e79d95daee93b60!2sSoma%20Avignon!5e0!3m2!1sen!2sfr!4v1775919094711!5m2!1sen!2sfr" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'

# ─── Prestations exemples ───
echo "→ Création des prestations..."
EXISTING_PRESTA=$($WP post list --post_type=prestation --format=ids 2>/dev/null)
if [ -z "$EXISTING_PRESTA" ]; then
  P1=$($WP post create --post_type=prestation --post_title="Massage Énergétique Crânien" --post_status=publish --post_excerpt="Un soin profond et apaisant qui libère les tensions du crâne et rééquilibre les énergies du corps. Idéal pour le stress, les migraines et le lâcher-prise." --menu_order=1 --porcelain)
  $WP post meta update "$P1" _soma_price "65€"
  $WP post meta update "$P1" _soma_duration "60 min"

  P2=$($WP post create --post_type=prestation --post_title="Massage Prénatal" --post_status=publish --post_excerpt="Un massage doux et enveloppant, spécialement conçu pour accompagner les futures mamans dans leur grossesse avec sérénité et confort." --menu_order=2 --porcelain)
  $WP post meta update "$P2" _soma_price "65€"
  $WP post meta update "$P2" _soma_duration "60 min"

  P3=$($WP post create --post_type=prestation --post_title="Massage Postnatal" --post_status=publish --post_excerpt="Un soin de reconnexion après l'accouchement pour aider le corps à retrouver son équilibre et offrir un moment de détente bien mérité." --menu_order=3 --porcelain)
  $WP post meta update "$P3" _soma_price "65€"
  $WP post meta update "$P3" _soma_duration "60 min"

  echo "  3 prestations créées"
else
  echo "  Prestations déjà existantes, skip"
fi

# ─── Témoignages exemples ───
echo "→ Création des témoignages..."
EXISTING_TEMO=$($WP post list --post_type=temoignage --format=ids 2>/dev/null)
if [ -z "$EXISTING_TEMO" ]; then
  T1=$($WP post create --post_type=temoignage --post_title="Marie L." --post_status=publish --post_content="Un moment de pure détente. Le massage crânien m'a permis de lâcher prise complètement. Je recommande les yeux fermés !" --porcelain)
  $WP post meta update "$T1" _soma_stars 5

  T2=$($WP post create --post_type=temoignage --post_title="Sophie D." --post_status=publish --post_content="Laurie est d'une douceur incroyable. Son massage prénatal m'a fait un bien fou pendant ma grossesse. Merci !" --porcelain)
  $WP post meta update "$T2" _soma_stars 5

  T3=$($WP post create --post_type=temoignage --post_title="Camille R." --post_status=publish --post_content="L'espace est chaleureux et apaisant. On se sent tout de suite en confiance. Une vraie parenthèse de bien-être." --porcelain)
  $WP post meta update "$T3" _soma_stars 5

  echo "  3 témoignages créés"
else
  echo "  Témoignages déjà existants, skip"
fi

echo ""
echo "╔══════════════════════════════════════╗"
echo "║  ✓ Setup terminé !                  ║"
echo "╚══════════════════════════════════════╝"
echo ""
echo "  Admin:    http://localhost:8090/wp-admin/"
echo "  Login:    admin / admin"
echo "  Site:     http://localhost:8090/"
echo ""
