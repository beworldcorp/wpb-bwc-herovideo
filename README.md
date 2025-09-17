# BWC HeroVideo (WPBakery)

Élément WPBakery pour afficher une **vidéo hero** (desktop/mobile) avec un **bandeau d’événements**.  
Shortcode : `[hero_video]`.

## Sommaire
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Mises à jour via Git Updater](#mises-à-jour-via-git-updater)
- [Utilisation](#utilisation)
  - [Depuis WPBakery](#depuis-wpbakery)
  - [En shortcode](#en-shortcode)
  - [Attributs](#attributs)
- [Structure](#structure)
- [Notes techniques](#notes-techniques)
- [Dépannage](#dépannage)
- [Versioning](#versioning)
- [Changelog](#changelog)
- [Licence](#licence)

---

## Prérequis
- **WordPress** ≥ 6.0  
- **PHP** ≥ 8.0  
- **WPBakery Page Builder** ≥ 6.x (Visual Composer *premium*)

## Installation
1. Copier le dossier du plugin dans :  
   `wp-content/plugins/BWC-herovideo/`
2. Activer l’extension : **Extensions → BWC HeroVideo** (ou “HomeVideo (WPBakery)” selon `Plugin Name`).
3. Vérifier que **WPBakery** est actif.

## Mises à jour via Git Updater
Le plugin est compatible **[Git Updater](https://github.com/afragen/git-updater)** (version gratuite).

Dans le fichier principal (`BWC-herovideo.php`) :
```php
/*
GitHub Plugin URI: beworldcorp/wpb-bwc-herovideo
Primary Branch: main
*/
