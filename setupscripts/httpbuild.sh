#!/bin/bash

if [ "$HTTPS_TOGGLE" == "1" ]
then
	cp -f /etc/httpd/httpd-ssl.conf /etc/httpd/conf/httpd.conf
	aws s3 cp s3://$S3_BUCKET_NAME/$S3_ENVIRONMENT/moodle/$S3_CERT_NAME /etc/pki/tls/certs/cert.pem
    aws s3 cp s3://$S3_BUCKET_NAME/$S3_ENVIRONMENT/moodle/$S3_PRIVATE_KEY /etc/pki/tls/certs/privatekey.pem
	
	if [ "$SSL_CHAIN_TOGGLE" == "1" ]
	then
		aws s3 cp s3://$S3_BUCKET_NAME/$S3_ENVIRONMENT/moodle/$S3_CHAIN_CERT /etc/pki/tls/certs/chain.pem
		chown apache:apache /etc/pki/tls/certs/chain.pem
		sed -i -e "s/#SSLCertificateChainFile/SSLCertificateChainFile/g" /etc/httpd/conf/httpd.conf
	fi
	
	chown apache:apache /etc/pki/tls/certs/cert.pem
	chown apache:apache /etc/pki/tls/certs/privatekey.pem
else 
	cp -f /etc/httpd/httpd-http.conf /etc/httpd/conf/httpd.conf
fi

sed -i -e "s/{WEB_HOSTNAME}/$RAW_HOSTNAME/g" /etc/httpd/conf/httpd.conf

echo "HTTPD Built"