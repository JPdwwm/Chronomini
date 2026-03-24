dev:
	docker compose up -d mysql_db
	make -j2 backend frontend

dev-stop:
	docker compose down

backend:
	cd ChronoMini && php artisan serve

frontend:
	cd ChronoMini-front && npm run dev
