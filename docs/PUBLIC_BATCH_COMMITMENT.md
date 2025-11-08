# Route publique pour la gestion des engagements (Batch Commitment)

## Vue d'ensemble

Une route publique a été créée pour permettre l'accès à la fonctionnalité de gestion des engagements sans nécessiter d'authentification. Cette route est sécurisée par signature cryptographique d'URL utilisant le composant UriSigner de Symfony.

## Configuration

### Secret de signature

Le secret de signature est configuré via une variable d'environnement :

```bash
# Dans .env.local ou variables d'environnement du serveur
URI_SIGNER_SECRET=votre_secret_securise_ici
```

**IMPORTANT** : En production, utilisez un secret fort et unique. Exemple de génération :
```bash
# Générer un secret sécurisé
openssl rand -hex 32
```

### Configuration des services

Le secret est configuré dans `config/services.yaml` :
```yaml
parameters:
    app.uri_signer_secret: '%env(default:default_uri_secret:URI_SIGNER_SECRET)%'
    default_uri_secret: 'CHANGE_ME_IN_PRODUCTION_WITH_STRONG_SECRET'

services:
    Symfony\Component\HttpFoundation\UriSigner:
        arguments:
            $secret: '%app.uri_signer_secret%'
```

## Utilisation

### Génération d'URLs signées

Utilisez la commande console pour générer des URLs signées :

```bash
# Générer une URL signée sans expiration
php bin/console app:generate-signed-url 1

# Générer une URL signée avec expiration (3600 secondes = 1 heure)
php bin/console app:generate-signed-url 1 --expiration=3600

# Générer une URL signée avec URL de base personnalisée
php bin/console app:generate-signed-url 1 --base-url=https://example.com
```

### Format des URLs signées

```
https://votre-domaine.com/public/batch-commitment/{candidateListId}?_hash=signature

# Avec expiration
https://votre-domaine.com/public/batch-commitment/{candidateListId}?_expiration=timestamp&_hash=signature
```

### Paramètres

- `candidateListId` : ID de la liste candidate à gérer
- `_hash` : Signature HMAC-SHA256 de l'URL (généré automatiquement)
- `_expiration` : Timestamp d'expiration (optionnel)

### Exemples d'URLs

```bash
# URL signée sans expiration
https://example.com/public/batch-commitment/1?_hash=PjfnXZhC_V2RnaSQxLVJEqLxlxTNbOAHLtFxSRU9ZE8

# URL signée avec expiration
https://example.com/public/batch-commitment/1?_expiration=1762639155&_hash=GvhATEPQX841TgvFeYwEe_KIVzOnySgDWrngDGGtV_0
```

## Fonctionnalités

La route publique offre les mêmes fonctionnalités que la route d'administration :

1. **Affichage des propositions** : Liste toutes les propositions avec leurs détails
2. **Gestion des engagements** : Permet d'accepter, refuser ou modifier les engagements
3. **Commentaires** : 
   - Commentaire global pour la liste candidate
   - Commentaires spécifiques par proposition
4. **Validation** : Même validation que la route d'administration (limite de 1000 caractères)

## Sécurité

### Protection par signature cryptographique

- Signature HMAC-SHA256 de l'URL complète
- Validation automatique à chaque requête (GET et POST)
- Protection contre la falsification d'URLs
- Support optionnel d'expiration automatique des URLs

### Contrôles d'accès

- Route accessible sans authentification (`PUBLIC_ACCESS`)
- Validation stricte du token avant tout traitement
- Gestion d'erreurs sécurisée (pas de fuite d'information)

### Messages d'erreur

- `Token de sécurité manquant` : Aucun token fourni
- `Token de sécurité non configuré` : Configuration manquante côté serveur
- `Token de sécurité invalide` : Token incorrect
- `Liste candidate non trouvée` : ID invalide

## Différences avec la route d'administration

### Avantages de la route publique

1. **Pas d'authentification requise** : Accès direct avec le token
2. **URL simple** : Peut être partagée facilement
3. **Interface dédiée** : Template adapté sans contexte d'administration

### Limitations

1. **Accès par token uniquement** : Nécessite la connaissance du token
2. **Pas d'interface de navigation** : Accès direct à une liste spécifique
3. **Pas de gestion des utilisateurs** : Fonctionnalité limitée aux engagements

## Template

Le template utilisé est `templates/public/batch_commitment.html.twig` (à créer), adapté pour :

- Fonctionner sans le contexte EasyAdmin
- Inclure le token dans tous les formulaires
- Interface simplifiée et responsive
- Messages flash pour les retours utilisateur

## Maintenance

### Rotation du token

Pour changer le token en production :

1. Mettre à jour la variable d'environnement `BATCH_COMMITMENT_TOKEN`
2. Redémarrer l'application si nécessaire
3. Mettre à jour les URLs partagées avec le nouveau token

### Monitoring

- Surveiller les tentatives d'accès avec des tokens invalides
- Logs d'erreur disponibles dans les logs Symfony
- Possibilité d'ajouter des métriques de sécurité

## Exemple d'intégration

### Dans un email automatique

```html
<p>Bonjour,</p>
<p>Vous pouvez gérer vos engagements en cliquant sur le lien suivant :</p>
<a href="https://votre-domaine.com/public/batch-commitment/123?token=abc123">
    Gérer mes engagements
</a>
```

### Dans une API

```php
$url = $this->generateUrl('public_batch_commitment', [
    'candidateListId' => $candidateList->getId(),
    'token' => $this->getParameter('app.batch_commitment_token')
]);
```
