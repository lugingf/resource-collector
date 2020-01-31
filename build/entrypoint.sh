#!/usr/bin/env bash

# Получение переменных среды, которые нужны за пределами php-fpm
if [[ -f ${APP_ROOT}/.env ]]; then
    export DEBUG=${DEBUG:-$(cat ${APP_ROOT}/.env | grep 'DEBUG=' | awk '{sub(/=/," ")}1' | awk '{print $2}')}
    export APP_ETCD=${APP_ETCD:-$(cat ${APP_ROOT}/.env | grep 'APP_ETCD=' | awk '{sub(/=/," ")}1' | awk '{print $2}')}
fi

export XDEBUG_REMOTE_ENABLE=${XDEBUG_REMOTE_ENABLE:-0}
export XDEBUG_REMOTE_PORT=${XDEBUG_REMOTE_PORT:-9000}
export XDEBUG_REMOTE_HOST=${XDEBUG_REMOTE_HOST:-}
export XDEBUG_REMOTE_CONNECT_BACK=${XDEBUG_REMOTE_CONNECT_BACK:-1}
envsubst < ${APP_ROOT}/build/php/xdebug.ini > ${PHP_CONF_DIR}/conf.d/xdebug.ini

if [[ ! -v OPCACHE_VALIDATE_TIMESTAMPS ]] && [[ "${DEBUG}" == "0" ]]; then
    OPCACHE_VALIDATE_TIMESTAMPS=0
fi
export OPCACHE_ENABLED=${OPCACHE_ENABLED:-1}
export OPCACHE_VALIDATE_TIMESTAMPS=${OPCACHE_VALIDATE_TIMESTAMPS:-1}
export OPCACHE_MEMORY_CONSUMPTION=${OPCACHE_MEMORY_CONSUMPTION:-128}
envsubst < ${APP_ROOT}/build/php/opcache.ini > ${PHP_CONF_DIR}/conf.d/opcache.ini

envsubst < ${APP_ROOT}/build/php/php.app.ini > ${PHP_CONF_DIR}/conf.d/99-app.ini

ETCD_INIT=${APP_ROOT}/build/etcd/etcd_init.sh
if [[ -f ${ETCD_INIT} ]]; then
    echo "---> Init etcd ..."
    bash ${ETCD_INIT}
fi

BOOTSTRAP=${APP_ROOT}/bin/bootstrap.sh
if [[ -f ${BOOTSTRAP} ]]; then
    echo "---> Run application bootstrap (bin/bootstrap.sh) ..."
    bash ${BOOTSTRAP}
fi

/usr/bin/supervisord -n -c /etc/supervisord.conf
