FROM nginx:latest

COPY ./stubs/nginx.conf /etc/nginx/conf.d/default.conf