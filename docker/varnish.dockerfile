FROM uselagoon/varnish-6

USER root
COPY ./docker/varnish/adv_varnish.vcl /etc/varnish/default.vcl
RUN fix-permissions /etc/varnish/
