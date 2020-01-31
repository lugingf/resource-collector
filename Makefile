PROJECT?=hostinfo
APP?=resource-collector
PORT?=8080
PORT_METRICS?=9102
APP_ETCD?=172.17.0.1:2379


CONTAINER_IMAGE?=$(PROJECT)/${APP}
RELEASE?=0.0.1

container:
	docker build -t $(CONTAINER_IMAGE):$(RELEASE) .

run: container
	docker stop $(CONTAINER_IMAGE):$(RELEASE) || true && docker rm $(CONTAINER_IMAGE):$(RELEASE) || true
	docker run --name ${APP} -p ${PORT}:8080 -p ${PORT_METRICS}:9102 --rm -ti \
		-e "APP_ETCD=${APP_ETCD}" \
		-v `pwd`:/var/www/html \
		$(CONTAINER_IMAGE):$(RELEASE)

test:
	php vendor/bin/phpunit -c phpunit.xml
