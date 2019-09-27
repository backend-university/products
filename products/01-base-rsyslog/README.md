## #01 Реализация централизованного хранения логов 

Продукт демонстрирует как реализовать хранение логов на выделенном сервере с клиентских машин.

См. [rsyslog](https://en.wikipedia.org/wiki/Rsyslog) для справки.

## Назначение

Данный продукт может быть полезен, когда необходимо реализовать единое хранение журналов с клиентских машин.

В данном случае мы реплицируем логи с клиентов для их доступности и возможности изучения инцидентов.  

## Внедрение

Чтобы быстро внедрить этот продукт, вам необходимо выполнить следующие настройки на стороне сервера для хранения логов и на клиентских машинах.

Сначала настройте сервер.

```shell script
curl -o server-$(hostname).conf -LJO https://raw.githubusercontent.com/backend-university/products/p01_base-rsyslog/products/01-base-rsyslog/server-hostname.conf
sudo mv server-$(hostname).conf /etc/rsyslog.d/server-$(hostname).conf
sudo service rsyslog restart
```

Теперь настройте клиент.

```shell script
curl -o client-$(hostname).conf -LJO https://raw.githubusercontent.com/backend-university/products/p01_base-rsyslog/products/01-base-rsyslog/client-hostname.conf
# Установите IP адрес вашего сервера для хранения логов.
sed -i 's/IP/your IP/g' client-$(hostname).conf
sudo mv client-$(hostname).conf /etc/rsyslog.d/client-$(hostname).conf 
sudo service rsyslog restart
```

## Итого

После того, как мы настроили сервер для логов и клиентские машины, мы можем проверить, что логи реплицируются на сервер.

Проверим, что на сервере появлись новые директории для логов от подключенных машин.

```shell script
ls -l /var/log/client-log/
``` 