
.PHONY: up down restart clean migrate migrate-local logs ps

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

migrate-local: ## Run database migrations locally in Docker
	@echo "Running database migrations..."
	docker-compose -f ./docker-compose.yml exec backend bin/console doctrine:schema:update --force
	@echo "Migrations completed!"

migrate: ## Run database migrations in the Symfony container inside the erebor pod (production)
	@echo "Running database migrations..."
	@POD_NAME=$$(kubectl get pods -l app.kubernetes.io/name=app -o jsonpath='{.items[0].metadata.name}'); \
	kubectl exec -it $$POD_NAME -c symfony -- bin/console d:s:u --force
	@echo "Migrations completed!"
