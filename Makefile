PROJECT_NAME := template-symfony
DOMAIN_BASE_URL := template-symfony.com
ECR_BASE_URL := 000000000000.dkr.ecr.us-east-1.amazonaws.com

.PHONY: up
up:
	@docker compose -f docker/docker-compose.yaml --env-file docker/.env up -d --remove-orphans

.PHONY: up-dev
up-dev:
	@docker compose -f docker/docker-compose.dev.yaml --env-file docker/.env up -d --remove-orphans

.PHONY: down
down:
	@docker compose -f docker/docker-compose.yaml --env-file docker/.env down


.PHONY: down-dev
down-dev:
	@docker compose -f docker/docker-compose.dev.yaml --env-file docker/.env down

.PHONY: build
build:
	@docker compose -f docker/docker-compose.yaml --env-file docker/.env build --no-cache

.PHONY: exec
exec:
	@docker exec -it ${PROJECT_NAME}.app ash

.PHONY: test
test:
	@docker exec -it ${PROJECT_NAME}.app bin/console d:s:u -f --env test && \
	docker exec -it ${PROJECT_NAME}.app bin/phpunit

.PHONY: csfix
csfix:
	@docker exec -it ${PROJECT_NAME}.app tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: loc
loc:
	@find ./app/src ./app/test -name '*.php' | xargs wc -l | tail -1

.PHONY: newcert
newcert:
	@docker exec -it ${PROJECT_NAME}.nginx certbot --nginx -d ${DOMAIN_BASE_URL} -d www.${DOMAIN_BASE_URL}

.PHONY: renewcert
renewcert:
	@docker exec -it ${PROJECT_NAME}.nginx certbot renew --quiet

.PHONY: ecrlogin
ecrlogin:
	@aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin ${ECR_BASE_URL}

.PHONY: docker-build-app
docker-build-app:
	@echo "Ingresa tag de la imágen Docker (app/app-worker):" && \
	read DOCKER_IMAGE_TAG && \
	docker build  docker/app -t ${ECR_BASE_URL}/${PROJECT_NAME}-app:$${DOCKER_IMAGE_TAG} && \
	docker build  docker/app-worker -t ${ECR_BASE_URL}/${PROJECT_NAME}-app-worker:$${DOCKER_IMAGE_TAG}

.PHONY: docker-pull-app
docker-pull-app:
	@echo "Ingresa tag de la imágen Docker (app/app-worker):" && \
	read DOCKER_IMAGE_TAG && \
	docker pull ${ECR_BASE_URL}/${PROJECT_NAME}-app:$${DOCKER_IMAGE_TAG} && \
	docker pull ${ECR_BASE_URL}/${PROJECT_NAME}-app-worker:$${DOCKER_IMAGE_TAG}

.PHONY: docker-push-app
docker-push-app:
	@echo "Ingresa tag de la imágen Docker (app/app-worker):" && \
	read DOCKER_IMAGE_TAG && \
	docker push ${ECR_BASE_URL}/${PROJECT_NAME}-app:$${DOCKER_IMAGE_TAG} && \
	docker push ${ECR_BASE_URL}/${PROJECT_NAME}-app-worker:$${DOCKER_IMAGE_TAG}

.PHONY: docker-build-nginx
docker-build-nginx:
	@echo "Ingresa tag de la imágen Docker (nginx):" && \
	read DOCKER_IMAGE_TAG && \
	docker build  docker/nginx -t ${ECR_BASE_URL}/${PROJECT_NAME}-nginx:$${DOCKER_IMAGE_TAG}

.PHONY: docker-pull-nginx
docker-pull-nginx:
	@echo "Ingresa tag de la imágen Docker (nginx):" && \
	read DOCKER_IMAGE_TAG && \
	docker pull ${ECR_BASE_URL}/${PROJECT_NAME}-app:$${DOCKER_IMAGE_TAG}

.PHONY: docker-push-nginx
docker-push-nginx:
	@echo "Ingresa tag de la imágen Docker (nginx):" && \
	read DOCKER_IMAGE_TAG && \
	docker push ${ECR_BASE_URL}/${PROJECT_NAME}-app:$${DOCKER_IMAGE_TAG}

.PHONY: deploy
deploy:
	@git pull origin master && \
	docker compose -f docker/docker-compose.yaml --env-file docker/.env up -d --remove-orphans && \
	docker exec -t ${PROJECT_NAME}.app composer install --no-dev -o && \
	docker exec -t ${PROJECT_NAME}.app-worker bin/console messenger:stop-workers && \
	docker restart ${PROJECT_NAME}.nginx
