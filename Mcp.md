### BK-Pesee

- **Important !**
    > Le principe
        > L'administrateur système crée l'entreprise et l'utilisateur via le web pour ensuite donné les informations comme le `codeentreprise` et de l'authentification au client
        > La partie `Desktop`
            > L'application une par pont bascule pousse leurs pesées via `/api/synchronisation` et le préfixe ou les 3 premières lettres du code du site qu'il envoi doit être celui de l'entreprise pour qu'on lie le site à l'entreprise..
                > La logique `SOF010` → préfixe `SOF` → on cherche une `Entreprise` dont le `codeentreprise` commence par `SOF`
            > !! récupère des listes de référentiels via `/api/client`, `/api/fournisseur`.. pour alimenter leurs formulaires
            > !! filtrer les opérations via `/api/lister`
        > !! `Frontend`
            > L'utilisateur se connecte à son compte pour avoir accès au tableau de bord pour voir les données de son entreprise
            > Ensuite des appels sont faites avec le `jwt`..
                > `/api/frontend/operations/stats` pour les totaux par site, par produit, par période..
                > `/api/frontend/operations` et `/api/frontend/sites` pour liste paginée, filtrée des opérations et la liste des sites de l'entreprise connectée

    > Les endpoints de l'api
        > L'endpoint `/api/synchronisation` pour la réception des données depuis les appareils
        > !! `/api/lister` pour la liste filtrée des pesées avec total poids net dont les référenciels sont..
            > `/api/site` pour la liste des ponts bascule par code, `/api/mouvement`, `/api/client`, `/api/fournisseur`, `/api/transporteur`, `/api/produit`, `/api/destination`, `/api/provenance`, `/api/vehicule` les données de référence pour les filtres
- - 