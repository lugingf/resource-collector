#!/usr/bin/env bash

echo "    ---> Run etcd bootstrap ..."
BOOTSTRAP_DIR=${APP_ROOT}/bootstrap/
types=(service business)
for type in "${types[@]}"; do
    ROOT_KEY=http://${APP_ETCD}/v2/keys/config-tutu/ResourceCollector/${type}
    dir_status=$(curl -I -w "%{http_code}" -s -o /dev/null ${ROOT_KEY})
    if [[ "${dir_status}" -eq "404" ]]; then
      curl -sS ${ROOT_KEY} -XPUT -d dir=true
    fi
    if [[ ! -f ${BOOTSTRAP_DIR}/${type}.config ]]; then
        continue
    fi
    cat ${BOOTSTRAP_DIR}/${type}.config | grep '.=.' | while read line; do
      key=$(echo $line | awk '{sub(/=/," ")}1' | awk '{print $1}')
      value=$(echo $line | awk '{sub(/=/," ")}1' | awk '{print $2}')
      node_status=$(curl -I -w "%{http_code}" -s -o /dev/null ${ROOT_KEY}/${key})
      if [[ "${node_status}" -eq "404" ]]; then
        curl -sS ${ROOT_KEY}/${key} -XPUT --data-urlencode "value=${value}"
      fi
    done
done

echo "    ---> Init confd ..."
cp build/etcd/confd/conf.d/* /etc/confd/conf.d/
cp build/etcd/confd/templates/* /etc/confd/templates/
cp build/etcd/confd/supervisor.confd.conf /etc/supervisord.d/confd.conf

echo "    ---> Init config from etcd ..."
/usr/local/sbin/confd -backend etcd -node http://${APP_ETCD} -onetime
