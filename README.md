# API VolleyTrack
Gestion d’équipe de volley-ball – API de démonstration

## Présentation du Projet
API VolleyTrack est une solution innovante pour la gestion complète d’une équipe de volley-ball. Conçu pour répondre aux besoins des entraîneurs et des gestionnaires sportifs, ce projet intègre deux API complémentaires :

- API d’authentification : sécurise l’accès à l’ensemble du système grâce à l’utilisation de tokens JWT.

- API de gestion d’équipe : permet de gérer de manière fluide et efficace les joueurs, les matchs, les feuilles de match et les statistiques.

Réalisé dans le cadre du projet R4.01 par le groupe B6, ce système a été pensé pour offrir une expérience utilisateur intuitive, que ce soit via des requêtes directes sur le serveur ou via une interface web dédiée.

---

## Fonctionnalités Clés
- Sécurité Renforcée

    - Gestion des connexions et des sessions via JWT, garantissant un accès sécurisé et fiable.

- Gestion des Joueurs

    - Création, modification et suppression de joueurs.

    - Consultation des données complètes (identifiants, informations personnelles, statut, etc.).

- Gestion des Matchs

    - Programmation des rencontres avec informations sur la date, l’heure, l’équipe adverse et le lieu (domicile/extérieur).

    - Mise à jour des scores en respectant les règles officielles du volley-ball.

- Gestion des Feuilles de Match

    - Attribution des rôles pour chaque joueur lors d’un match (Attaquant, Passeur, Centre, Libero, Remplaçant).

    - Enregistrement des performances avec notation individualisée.

- Statistiques d’Équipe

    - Accès aux statistiques détaillées des joueurs et des matchs pour un suivi optimal des performances.

--- 

## Architecture & Technologies

- RESTful API

    - Les échanges se font via le format JSON, assurant une intégration simple et flexible avec divers clients.

- Sécurité

    - Authentification robuste basée sur JWT pour protéger les données sensibles. Fait par nos soins (API-Authentification)

- Endpoints Structurés

    - Un ensemble d’URL claires pour interagir avec les différentes ressources :

        - /authapi.php pour l’authentification

        - /feuillematch_endpoint.php pour les feuilles de match

        - /joueurs_endpoint.php pour la gestion des joueurs

        - /matchs_endpoint.php pour la gestion des matchs

        - /stats_endpoint.php pour les statistiques

---

## Liens Utiles
Dépôt GitHub - API d’Authentification : https://github.com/ClementReverbel/API-Authentification

Dépôt GitHub - API VolleyTrack : https://github.com/ClementReverbel/API-VolleyTrack

Endpoint Authentification : https://authapi.alwaysdata.net

Endpoint Gestion d’équipe : https://volleyapi.alwaysdata.net/ressources

---

### Projet réalisé par REVERBEL Clément et REYNIER Zyad – Groupe B6, Mars 2025.