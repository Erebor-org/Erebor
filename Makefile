
.PHONY: up down restart clean migrate migrate-local migrate-status logs ps test-db test

up: ## Start the application
	docker-compose -f ./docker-compose.yml up -d
	@echo "Access UI: http://localhost:5173"
	@echo "Access Symfony: http://localhost:8000"

down: ## Stop the application
	docker-compose -f ./docker-compose.yml down

restart: ## Restart the application
	docker-compose -f ./docker-compose.yml restart

clean: ## Clean up containers and networks (keeps volumes)
	docker-compose -f ./docker-compose.yml down --remove-orphans
	docker system prune -f

logs: ## View logs from all services
	docker-compose -f ./docker-compose.yml logs -f

ps: ## Show running containers
	docker-compose -f ./docker-compose.yml ps

# Migrations : backend/bin/deploy-migrate.sh (baseline des anciennes migrations non
# rejouables + doctrine:migrations:migrate). La CD le lance automatiquement à chaque déploiement.
# Jamais de doctrine:schema:update --force : il supprimerait les tables non mappées qui
# contiennent des données (events, event_participants, skin_submissions, skin_votes).
PROD_POD = $$(kubectl -n erebor get pods -l app.kubernetes.io/name=app --field-selector=status.phase=Running -o jsonpath='{.items[0].metadata.name}')

migrate-status: ## Show which migrations are recorded as executed in production
	@kubectl -n erebor exec $(PROD_POD) -c symfony -- bin/console doctrine:migrations:status

migrate-local: ## Apply pending Doctrine migrations locally in Docker
	docker-compose -f ./docker-compose.yml exec backend sh bin/deploy-migrate.sh

migrate: ## Apply pending Doctrine migrations in production (normally done by the CD)
	kubectl -n erebor exec $(PROD_POD) -c symfony -- sh bin/deploy-migrate.sh

TEST_DB_URL = postgresql://erebor:erebor@postgres-test:5432/erebor?serverVersion=16&charset=utf8

test-db: ## Start the throwaway test Postgres and (re)create the schema from the entities
	docker-compose -f ./docker-compose.yml --profile test up -d postgres-test
	@until docker-compose -f ./docker-compose.yml exec -T postgres-test pg_isready -U erebor -d erebor_test >/dev/null 2>&1; do sleep 1; done
	docker-compose -f ./docker-compose.yml exec -T -e APP_ENV=test -e 'DATABASE_URL=$(TEST_DB_URL)' backend php bin/console doctrine:schema:drop --force --full-database -q
	docker-compose -f ./docker-compose.yml exec -T -e APP_ENV=test -e 'DATABASE_URL=$(TEST_DB_URL)' backend php bin/console doctrine:schema:create -q

test: ## Run the backend test suite (run `make test-db` once before)
	docker-compose -f ./docker-compose.yml exec -T backend php bin/phpunit
