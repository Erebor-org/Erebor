.PHONY: up

up: ## Start the application
	docker-compose -f ./docker-compose.yml up -d
	@echo "Access UI: http://localhost:5173"
	@echo "Access Symfony: http://localhost:8000"
