# Reproduce

Start Database:

```
docker compose up
```

Install fixtures:

```bash
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:schema:create
bin/console doctrine:fixtures:load
```

Start Webserver:

```php
php -S 127.0.0.1:8000 -t public
```

Call endpoint:

```
curl -XGET 'https://127.0.0.1:8000/'
```

Outputs:

> FAIL: second flush triggered clear query again

Expected:

> OK: two customers added
