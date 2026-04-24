# 📋 Checklist de Recette PDF Custom (ONR Negoce)

Ce document décrit les points de vérification obligatoires avant la mise en production des modèles PDF personnalisés.

## 🛠️ Tests Communs (Tous Modules)
- [ ] **Activation** : Le modèle `onrnegoce_...` est bien sélectionnable dans la configuration du module.
- [ ] **Visual Identity** : Le logo est net, sans distorsion. Le bleu (#1A2B5E) est correct.
- [ ] **Marges** : Pas de texte "collé" aux bords. Respect de la zone de 10mm.
- [ ] **Pagination** : Sur un document long, l'en-tête et le pied de page restent cohérents (Page X/Y).
- [ ] **Accents & Symboles** : Test avec des caractères spéciaux (é, à, €, ç, ", &).
- [ ] **Performance** : Temps de génération < 2 secondes pour un document standard.

## 📄 Module : Facturation (`onrnegoce_facture`)
- [ ] **Calculs** : Total HT + TVA = Total TTC affiché.
- [ ] **Extrafields** : La référence de commande client (`ref_commande_client`) s'affiche si renseignée.
- [ ] **Extrafields** : Le mode de livraison s'affiche correctement.
- [ ] **Signature** : Si l'attribut `show_signature` est coché, la zone de signature apparaît.

## 🤝 Module : Devis (`onrnegoce_propale`)
- [ ] **Signature** : La zone "Bon pour accord" est présente par défaut.
- [ ] **Style** : Italique pour les services et lignes alternées #F5F7FA.

## 📦 Module : Bons de Livraison (`onrnegoce_livraison`)
- [ ] **Confidentialité** : AUCUN prix n'apparaît sur le document.
- [ ] **Barcode** : Le code-barres en haut à droite est présent.
- [ ] **Checkboxes** : Les cases "OK" sont bien alignées pour la réception.
- [ ] **Poids** : Affichage du Poids Total si renseigné.

## 🚨 Tests de "Bord" (Edge Cases)
- [ ] **Zéro Ligne** : Générer un document sans aucune ligne (pas d'erreur PHP).
- [ ] **Gros Volume** : Test avec un document de **50+ lignes**.
- [ ] **Désignations longues** : Produit avec une description de plusieurs paragraphes.

---
*Dernière mise à jour : Avril 2026*
