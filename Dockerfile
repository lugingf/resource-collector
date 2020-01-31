# https://stash.tutu.ru/projects/PS/repos/openshift_docker_php_builder/browse
FROM registry.ci.tutu.ru/openshift/php-builder:7.3-alpine as build

ADD . /app/
RUN cd /app && mkdir -p ~/.ssh && (cp .ssh/id_rsa ~/.ssh/id_rsa && chmod 600 ~/.ssh/id_rsa || true) && \
    git config --global core.sshCommand "ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" && \
    composer --no-interaction --no-ansi --optimize-autoloader --no-dev install && \
    rm -fr .ssh ~/.ssh

# https://stash.tutu.ru/projects/PS/repos/openshift_docker_php_fpm_nginx/browse
FROM registry.ci.tutu.ru/openshift/php-fpm-nginx:7.3-alpine-v20191115-130719 as production

ENV APP_ROOT /var/www/html
ADD build/nginx/nginx.app.conf /etc/nginx/conf.d/app.conf

COPY --from=build /app ${APP_ROOT}
RUN chgrp -R 0 ${APP_ROOT} && \
    chmod -R g=u ${APP_ROOT}

CMD build/entrypoint.sh
WORKDIR ${APP_ROOT}
USER 1001
