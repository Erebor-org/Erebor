#!/bin/sh
# Applique les migrations Doctrine en attente. Lancé par la CD (.github/workflows/deploy.yml)
# dans le conteneur symfony après chaque déploiement ; peut aussi être lancé à la main
# (make migrate).
#
# L'historique des migrations de prod ne reflète pas fidèlement le schéma (il a longtemps été
# géré par doctrine:schema:update). Donc :
#  1. les migrations historiques NON rejouables (CREATE TABLE nus, DROP de tables) sont
#     seulement marquées comme exécutées, jamais lancées — leur schéma est déjà en prod ;
#  2. toutes les autres sont idempotentes (IF NOT EXISTS…) et passent par migrations:migrate.
# Toute nouvelle migration doit être écrite rejouable (IF NOT EXISTS / DO $$ … EXCEPTION).
set -eu

cd "$(dirname "$0")/.."
console() { php bin/console --no-interaction "$@"; }

BASELINE="20241130095500 20241202210123 20250426164606 20260819115441"

console doctrine:migrations:sync-metadata-storage

for v in $BASELINE; do
    if out=$(console doctrine:migrations:version "DoctrineMigrations\\Version$v" --add 2>&1); then
        echo "Baseline: Version$v marquée comme exécutée (non lancée)."
    elif echo "$out" | grep -qi "already exists"; then
        : # déjà enregistrée
    else
        echo "$out" >&2
        exit 1
    fi
done

console doctrine:migrations:migrate --allow-no-migration
console doctrine:migrations:status | tail -n 20
