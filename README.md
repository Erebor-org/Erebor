
# 📢 Release Note - Gestionnaire Officiel de la Guilde Dofus

## ✨ Présentation Générale
Bienvenue sur le site officiel de gestion de notre guilde sur **Dofus** ! Ce site a été spécialement conçu pour centraliser toutes les informations importantes concernant nos membres, tout en automatisant certaines tâches clés et en facilitant la gestion quotidienne de la guilde.

Le site repose sur les technologies **Symfony (backend)**, **VueJS (frontend)** et **PostgreSQL (base de données)**, avec l'intégration de **n8n** pour automatiser certaines notifications Discord.

---

## 🚀 Fonctionnalités Détaillées

### 🔐 Authentification Sécurisée
- Système complet d'inscription et connexion sécurisé.

---

### 📋 Gestion des Membres & Personnages

#### 📥 Importation Facile de Membres
- Importation rapide d’un nouveau membre avec les informations suivantes :
  - Pseudo du personnage.
  - Pseudo Ankama.
  - Classe.
  - Date de recrutement.
  - Personnage recruteur.
- **Vérification automatique** si le joueur est blacklisté avant validation de l’import.
- Protection contre les doublons : impossibilité d'importer un personnage avec un pseudo ou pseudo Ankama déjà existant.

---

#### 🖼️ Modification Directe depuis la Liste des Membres
- **Changement rapide de classe** :
  - Un simple **clic sur l'image de classe** permet de modifier la classe du personnage sans quitter la page.
- **Modification du nom du personnage** :
  - En cliquant directement sur le nom du personnage, il est possible de le modifier en temps réel.

---

#### 📜 Affichage et Consultation Simplifiés
- Liste complète des membres affichée sous forme de tableau :
  - Classe, rang, ancienneté, recruteur, mules associées.
  - Statut actif ou archivé clairement visible.
- Tri et recherche facilités pour retrouver rapidement un membre spécifique.

---

### 🏅 Système de Rangs Automatisé
- Attribution automatique du **rang de guilde** selon l’ancienneté du joueur.
- Notification envoyée automatiquement sur Discord lorsqu’un membre est éligible à un changement de rang (via automatisation).

---

### ⚔️ Gestion des Mules
- Possibilité d’associer plusieurs mules à un personnage principal.
- Ajout ou suppression de mules en quelques clics.
- Les mules apparaissent clairement liées à leur personnage principal.

---

### 🗃️ Archivage des Membres
- Un membre quittant la guilde peut être **archivé** facilement, tout en conservant ses données consultables à tout moment.

---

### 🚫 Système Complet de Blacklist
- Ajout d’un joueur à la blacklist avec possibilité de renseigner le motif.
- Lors de l’importation d’un nouveau personnage, le site vérifie automatiquement si le joueur est blacklisté et empêche son recrutement.
- Consultation et gestion facile de la blacklist.

---

### 🔔 Automatisation Discord
- Notifications automatiques dans un channel Discord dédié lorsque :
  - Un membre atteint le seuil d’ancienneté pour monter en rang.
  - Toute autre action importante nécessitant l’attention des officiers.

---

## 🛠️ Endpoints API Principaux

### 📌 Characters
- `GET /characters` → Liste des membres.
- `POST /characters/import` → Importation d'un personnage.
- `PATCH /characters/{id}/archive` → Archivage d’un personnage.

### 📌 Mules
- `POST /mule/add` → Ajout d'une mule.
- `DELETE /mule/{id}` → Suppression d’une mule.

### 📌 Rangs
- `GET /ranks` → Liste des rangs.
- `POST /ranks` → Création ou modification d’un rang.
- `DELETE /ranks/{id}` → Suppression d’un rang.

### 📌 Blacklist
- `GET /blacklist` → Consultation de la blacklist.
- `POST /blacklist/add` → Ajout d’un joueur à la blacklist.
- `DELETE /blacklist/{id}` → Suppression d’un joueur de la blacklist.

---

## 🖥️ Expérience Utilisateur Optimisée
- **Modification en un clic directement dans le tableau des membres :**
  - Changement du nom ou de la classe du personnage sans avoir à ouvrir de formulaire complexe.
- Interface fluide et intuitive.
- Informations essentielles toujours visibles d’un coup d’œil.

---

## 🌱 Prochaines pistes d’amélioration (Suggestions)
- Statistiques globales de la guilde : nombre total de membres, répartition par classe et rang.
- Système de gestion des candidatures en ligne.
- Organisation d’événements (raids, sorties) directement via le site.
- Exportation des données des membres en CSV ou PDF.
- API publique pour consultation externe.

---

## ✅ Conclusion
Ce site a été conçu pour simplifier et fluidifier la gestion de notre guilde tout en assurant un suivi rigoureux des membres. Grâce à l’automatisation et à une interface soignée, il permet aux officiers et aux membres d’avoir toutes les informations à portée de main et de gérer la guilde efficacement, sans prise de tête.





## Commande pour démarrer symfony

### Démarrer le container

```
dc up -d backend
dc exec backend bash
symfony server:start
```
