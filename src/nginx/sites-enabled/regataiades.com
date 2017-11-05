server {
	listen 80;
	server_name www.regataiades.com;
	return 301 $scheme://regataiades.com$request_uri;
}

server {
    	# listen 80 default_server;
    	# listen [::]:80 default_server ipv6only=on;

    	root /var/www/prod/regataiades;
    	index.en.html index.htm;

    	#server_name _;
	server_name regataiades.en;

    	location / {
        	try_files $uri $uri/ /index.php?$query_string;
    	}

    	location ~ \.php$ {
        	try_files $uri /index.php =404;
        	fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        	fastcgi_index index.php;
        	fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

	location ~ /\.ht {
		deny all;
    	}

	location ~* \.html$ {
 		 expires -1;
	}

	location ~* \.(css|js|gif|jpe?g|png|ico|woff|svg)$ {
		expires 365d;
		# add_header Pragma public;
		# add_header Cache-Control "public, must-revalidate, proxy-revalidate";
	}
}
