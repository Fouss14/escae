<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'escae' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'aq/H&.Y*kJ3&OB;#I)=A5D6x VhL%W!@iYlcdLB;9oy.gC]S;W[= ,;8<os*_|9*' );
define( 'SECURE_AUTH_KEY',  'JG!p(Cs)wJ!bq`sHn>3@^DS^jhkyc9/;DaOlA; <f#4TetYM?$4=t:4Ix!6rFNVf' );
define( 'LOGGED_IN_KEY',    'Zn@8zB)+@+v{)_K@TqE[(kHqMnmlFo+oR3[e&.I~}>`irrku6}e$ecd0e2Os ]uH' );
define( 'NONCE_KEY',        'HeJy.097j^yt|~=<+0>]2TZ<NxdCf&[(E{0ZP,Da)Hp*MTl`3dIBuoWw3DtNbZOK' );
define( 'AUTH_SALT',        'l08/J`al*O+*XX+LMedk^q#a)_@b)ubh$HDs2!Bd(&-D}K~e@5S#Wd;UQrzOh99!' );
define( 'SECURE_AUTH_SALT', 'T._:m_gN?x.^^b9yzV<s$|=y_zNAiOXSQKTy?6$zWo^ipc67xJ!9b|r>/53Ktr?/' );
define( 'LOGGED_IN_SALT',   'Gg.f[+i8So8;ajiQk4+ni>((oCtK[n<uz`__3g3H1_(2g~M,paY,4]@h+uJ $bG$' );
define( 'NONCE_SALT',       'Lw7<UD#]W7q@e--$i-F=T.kR!7qz|&)RP27eY780tZpruyO=fa*Uh<Wy6kfF*iCA' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
