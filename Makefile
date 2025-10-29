
.PHONY: up migrate

up: ## Start the application
	docker-compose -f ./docker-compose.yml up -d
	@echo "Access UI: http://localhost:5173"
	@echo "Access Symfony: http://localhost:8000"

migrate: ## Run database migrations in the Symfony container inside the erebor pod
	@echo "Running database migrations..."
	@POD_NAME=$$(kubectl get pods -l app.kubernetes.io/name=app -o jsonpath='{.items[0].metadata.name}'); \
	kubectl exec -it $$POD_NAME -c symfony -- bin/console d:s:u --force
	@echo "Migrations completed!"
