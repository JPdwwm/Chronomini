dev:
	make -j2 backend frontend

backend:
	cd ChronoMini && php artisan serve

frontend:
	cd ChronoMini-front && npm run dev
init-publish:
	docker context create chronomini-site --docker "host=ssh://root@82.29.169.38"
	docker context use chronomini-site
publish:
	docker context use chronomini-site
	docker-compose down --rmi all --remove-orphans
	docker system prune -a
	docker compose -f ./docker-stack.yml up 
publish-data:
	docker exec $(shell docker ps --filter "name=projetchronomini-laravel-docker-1" -q) bash -c "php artisan migrate:fresh --seed --force" 
update-publish:
	docker context use chronomini-site
	docker-compose down --rmi all
	docker compose -f ./docker-stack.yml up -d