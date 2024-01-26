### Setup ###
- run `docker network create push_service`
- run `docker compose up -d --build`
- run `cat setup/database.sql | docker exec -i php_server mysql -h push_database -u devel -pdevel push_db`
- run `docker exec -i php_server composer install`

To run a push send test
- `docker exec -i php_server php launch.php`
[With the provided test data this will fail with a 403 error]
