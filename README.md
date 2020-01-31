resource-collector
======================================

* TODO

Features
--------

* TODO

# Devel mode

1. Перейти в свой неймспейс (проект + окружение), например `oc project <projectname>-<incrementname>-<username>`
2. ```sh

    oc set probe $(oc get dc -l unit=resource-collector --sort-by='.metadata.creationTimestamp' -o name | tail -r | head -n 1) --remove --readiness --liveness

    oc set env $(oc get dc -l unit=resource-collector --sort-by='.metadata.creationTimestamp' -o name | tail -r | head -n 1) OPCACHE_VALIDATE_TIMESTAMPS=1

    oc rollout latest $(oc get dc -l unit=resource-collector --sort-by='.metadata.creationTimestamp' -o name | tail -r | head -n 1)

    oc rsync ./resource-collector/src/ $(oc get pods -l unit=resource-collector -o=name --sort-by='.metadata.creationTimestamp' | tail -r | head -n 1):/var/www/html/src --watch --no-perms
    ```
3. Кодить, результат смотреть в платформе (инкременте)
4. По готовности закоммитить — запустится пайплайн, лишние настройки перетрутся

# Подключение миграций Phinx

1. Подключить phinx через composer
    ```sh
    composer require robmorgan/phinx
    ```

2. Проинициализиорвать phinx `vendor/bin/phinx init --format=php`
В корне проекта появится файл phinx.php
3. Установить нужные значения в phinx.php
  - Папка с миграциями. Оставить как есть или переопределить своим значением. От переменной PHINX_CONFIG_DIR можно вообще избавиться или определить её в конфиге сервиса
  - Редактирование секции environments. Убрать среды development и testing, оставить только production. Выставить параметры подключения к БД можно 2-мя способами:
    - URI-like DSN: можно вытащить данные коннекшна средствами ORM или воспользоваться парсером типа [nyholm/dsn](https://github.com/Nyholm/dsn), скормить ему строку DSN из конфига
    - ODBC-like DSN (формат, с которым работает PDO): распарсить вручную или, опять же, вытащить данные коннекшна средствами ORM
4. В файл bin/bootstrap.php после загрузки среды прописать: 
	```php
	exec('vendor/bin/phinx migrate -e production -c '. __DIR__ . '/../phinx.php', $output, $returnCode);
	if ($returnCode !== 0) {
		throw new \Exception('migration was failed: ' . implode("\n", $output));
	}
	```
5. Написать саму миграцию и положить её в папку, которая прописана в phinx.php
6. Рестартануть сервис. Миграция выполнится
