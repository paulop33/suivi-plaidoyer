# Présentation Reveal.js - Plaidoyer Vélocité Municipales 2026

Cette présentation a été créée à partir du PDF `public/Plaidoyer_Municipales_2026_planches-2.pdf`.

Le contenu textuel du PDF a été extrait et structuré en HTML pour une présentation interactive et accessible.

## 📁 Contenu

- `index.html` - Fichier principal de la présentation Reveal.js avec contenu HTML structuré
- `slide-1.png` à `slide-9.png` - Images extraites du PDF (utilisées comme illustrations)

## 📋 Structure de la présentation

La présentation est organisée en **navigation 2D** avec des slides principales (horizontales) et des sous-slides (verticales) pour éviter la surcharge d'information.

**✨ Nouveauté** : Chaque engagement a maintenant sa propre page dédiée avec une **image illustrative** provenant d'internet pour un impact visuel maximal !

1. **Page de titre** - Pour une métropole marchable et cyclable
2. **Introduction** (4 sous-slides ⬇️)
   - Contexte
   - Les enjeux
   - Le vélo comme solution
   - Notre approche
3. **Circuler dans la Métropole** (4 sous-slides ⬇️)
   - Le ReVE en chiffres
   - Parcours de qualité
   - Avancement 2025
   - Carte du réseau
4. **Chiffres clés** (4 sous-slides ⬇️)
   - Parts modales et objectifs
   - Déplacements et VAE
   - Rues aux enfants
   - Potentiel du vélo
5. **Engagements Métropole** (9 sous-slides ⬇️) 📸
   - Introduction
   - 8 engagements avec images (un par page)
6. **Focus Commerce** (4 sous-slides ⬇️)
   - Introduction
   - Biais de perception
   - Mythe du panier moyen
   - Les vrais freins
7. **Circuler dans mon quartier** (2 sous-slides ⬇️)
   - Pourquoi apaiser ?
   - Illustration
8. **Quartiers apaisés** (7 sous-slides ⬇️) 📸
   - Introduction
   - 6 engagements avec images (un par page)
9. **Les piétons** (5 sous-slides ⬇️) 📸
   - Introduction
   - 4 engagements avec images (un par page)
10. **Les écoles** (4 sous-slides ⬇️) 📸
    - Introduction
    - 3 engagements avec images (un par page)
    - Exemples d'aménagements
11. **Intermodalité** (6 sous-slides ⬇️) 📸
    - Introduction
    - 5 engagements avec images (un par page)
12. **Guide aménagements** (10 sous-slides ⬇️) 📸
    - Introduction
    - 9 engagements avec images (un par page)
13. **Plan vélo** (5 sous-slides ⬇️) 📸
    - Introduction
    - 4 engagements avec images (un par page)
14. **Conclusion** - Merci

📸 = Sections avec engagements individualisés et images illustratives

## 🚀 Utilisation

### Option 1 : Ouvrir directement dans le navigateur

Ouvrez simplement le fichier `index.html` dans votre navigateur web :

```bash
# Depuis le répertoire racine du projet
open presentation/index.html
# ou
firefox presentation/index.html
# ou
google-chrome presentation/index.html
```

### Option 2 : Serveur web local

Pour une meilleure expérience, utilisez un serveur web local :

```bash
# Avec Python 3
cd presentation
python3 -m http.server 8000

# Avec PHP
cd presentation
php -S localhost:8000

# Avec Node.js (npx)
cd presentation
npx http-server -p 8000
```

Puis ouvrez votre navigateur à l'adresse : `http://localhost:8000`

### Option 3 : Via le serveur Symfony

Si vous utilisez déjà le serveur Symfony, la présentation est accessible via :
`http://localhost:8000/presentation/` (ou le port de votre serveur Symfony)

## ⌨️ Raccourcis clavier et Navigation

### Navigation 2D (horizontale et verticale)

La présentation utilise une **navigation en 2 dimensions** :
- **Flèche droite** ou **Espace** : Slide suivante (horizontale)
- **Flèche gauche** : Slide précédente (horizontale)
- **Flèche bas** : Sous-slide suivante (verticale) ⬇️
- **Flèche haut** : Sous-slide précédente (verticale) ⬆️

### Autres raccourcis

- **F** : Mode plein écran
- **S** : Mode présentateur (notes)
- **O** ou **Esc** : Vue d'ensemble des diapositives (très utile pour voir la structure 2D)
- **B** ou **.** : Écran noir (pause)
- **?** : Afficher l'aide des raccourcis

### Structure de navigation

Les slides principales (horizontales) sont :
1. Titre
2. Introduction (4 sous-slides verticales)
3. Le ReVE (4 sous-slides verticales)
4. Chiffres clés (4 sous-slides verticales)
5. Engagements Métropole (3 sous-slides verticales)
6. Commerce de proximité (4 sous-slides verticales)
7. Quartiers apaisés (2 sous-slides verticales)
8. Quartiers et Piétons (4 sous-slides verticales)
9. Les écoles
10. Intermodalité (3 sous-slides verticales)
11. Guide aménagements (3 sous-slides verticales)
12. Plan vélo (3 sous-slides verticales)
13. Conclusion

💡 **Astuce** : Appuyez sur **O** pour voir la vue d'ensemble et comprendre la structure complète !

## 🎨 Personnalisation

La présentation utilise un thème personnalisé aux couleurs de Vélocité :
- Vert principal : `#42b983`
- Couleur sombre : `#2c3e50`
- Fond clair : `#ecf0f1`

### Changer le thème de base

Dans `index.html`, modifiez la ligne du thème (ligne 11) :

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/reveal.js@5.1.0/dist/theme/white.css">
```

Thèmes disponibles : `black`, `white`, `league`, `beige`, `sky`, `night`, `serif`, `simple`, `solarized`, `blood`, `moon`

### Modifier les couleurs

Les couleurs personnalisées sont définies dans les variables CSS (lignes 14-18) :

```css
:root {
    --velocite-green: #42b983;
    --velocite-dark: #2c3e50;
    --velocite-light: #ecf0f1;
}
```

### Modifier les transitions

Dans la configuration JavaScript, changez :

```javascript
transition: 'slide', // none/fade/slide/convex/concave/zoom
```

## 📝 Notes techniques

- **Reveal.js version** : 5.1.0 (via CDN)
- **Résolution des images** : 150 DPI
- **Format des images** : PNG
- **Nombre de slides** : 13 (avec contenu HTML structuré)
- **Source** : Plaidoyer_Municipales_2026_planches-2.pdf
- **Extraction du texte** : pdftotext
- **Conversion PDF → images** : pdftoppm

## ✨ Fonctionnalités

- ✅ Contenu textuel extrait et structuré en HTML
- ✅ Navigation fluide entre les slides
- ✅ Design responsive (mobile, tablette, desktop)
- ✅ Thème personnalisé aux couleurs Vélocité
- ✅ Mise en page en colonnes pour certaines sections
- ✅ Encadrés pour les engagements et focus
- ✅ Statistiques mises en valeur
- ✅ Images du PDF utilisées comme illustrations
- ✅ Accessibilité améliorée (texte sélectionnable, lecteurs d'écran)

## 🔄 Régénération des images

Si vous devez régénérer les images à partir du PDF :

```bash
pdftoppm -png -r 150 public/Plaidoyer_Municipales_2026_planches-2.pdf presentation/slide
```

Options :
- `-r 150` : Résolution en DPI (augmentez pour plus de qualité)
- `-png` : Format de sortie PNG
- Vous pouvez aussi utiliser `-jpeg` pour des fichiers plus légers

## 📚 Documentation Reveal.js

Pour plus d'informations sur Reveal.js :
- Site officiel : https://revealjs.com/
- Documentation : https://revealjs.com/
- GitHub : https://github.com/hakimel/reveal.js

