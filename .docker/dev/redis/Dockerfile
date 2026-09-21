FROM redis:8.8.2-alpine3.23

RUN apk add --no-cache tzdata

ENV TZ=Europe/Kyiv
ENV HOME=/home/${USERNAME}

# Set time zone in container
RUN cp /usr/share/zoneinfo/Europe/Kyiv /etc/localtime \
  && echo "Europe/Kyiv" > /etc/timezone

# Import UID and GID from docker compose
ARG UID
ARG GID
ARG UMASK
ARG USERNAME

RUN set -eux; \
    if getent group "${USERNAME}" > /dev/null; then \
        groupmod -o -g "${GID}" "${USERNAME}"; \
    else \
        groupadd -o -g "${GID}" "${USERNAME}"; \
    fi; \
    if id "${USERNAME}" > /dev/null 2>&1; then \
        usermod -o -u "${UID}" -g "${GID}" -d "${HOME}" "${USERNAME}"; \
    else \
        useradd -o -u "${UID}" -g "${GID}" -d "${HOME}" -m -s /bin/sh "${USERNAME}"; \
    fi; \
    mkdir -p "${HOME}"; \
    chown -R "${UID}:${GID}" "${HOME}"

RUN mkdir -p \
    /data/ \
    /var/log/ \
  && touch \
    /var/log/redis.log \
  && chown -R ${UID}:${GID} \
    /data/ \
    /var/log/ \
  && chmod -R 755 \
    /data/ \
    /var/log/

COPY .docker/dev/redis/redis.conf /usr/local/etc/redis/redis.conf

# Copy custom entrypoint
COPY --chown=${UID}:${GID} .docker/dev/redis/entrypoint.sh /usr/local/bin/custom-entrypoint.sh

RUN chmod +x /usr/local/bin/custom-entrypoint.sh

USER ${USERNAME}

EXPOSE 6379

ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]

CMD ["redis-server", "/usr/local/etc/redis/redis.conf"]