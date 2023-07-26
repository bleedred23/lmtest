# Простой конструктор блюд.
#### Конструктор выполнен в виде консольного приложения.
### Инструкция по локальной установке проекта

*1. Установите docker и docker compose*

*2. Создайте файл ".env" и заполните HTTP_PORT / MYSQL_PORT (можно скопировать example.env)*

*3. Запустите обновление и установку зависимостей:*
```shell
docker-compose run --rm php composer update --prefer-dist
docker-compose run --rm php composer install    
```
*4. Запустите контейнеры с помощью команды:*
```shell
docker-compose up -d
```

*5. Выполните миграции:*
```shell
docker-compose exec php yii migrate
```

*6. Проверьте работу конструктора. Введите команду:*
```shell
docker-compose exec php yii construct {ingredients}
```
В {ingredients} нужно подставить строку, содержащую типы ингредиентов. Например:

```shell
docker-compose exec php yii construct diicc
```
