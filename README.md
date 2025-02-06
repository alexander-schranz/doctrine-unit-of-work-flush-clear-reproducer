# Reproducer that a clear is called twice if flush is called on event listener on postFlush

Related issue: [https://github.com/doctrine/orm/issues/11827](https://github.com/doctrine/orm/issues/11827)

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

# Workaround

Do not use `clear` method, I recommend following PHPStan rule:

By using [https://github.com/spaze/phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls):

```neon
includes: # if no extension installer is used:
    - vendor/spaze/phpstan-disallowed-calls/extension.neon
parameters:
    disallowedMethodCalls:
        -
            method: 'Doctrine\Common\Collections\Collection::clear()'
            message: 'This is buggy see: https://github.com/doctrine/orm/issues/11827'
            errorTip: 'use custom a customized replace method with removeElement and addElement'
```
