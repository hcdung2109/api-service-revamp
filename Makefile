run: up
## —— Docker
up: docker-compose.yml
	docker compose -f ./docker-compose.yml up --build -d
down: docker-compose.yml
	docker compose down
ssh:
	docker exec -it api-service /bin/sh
