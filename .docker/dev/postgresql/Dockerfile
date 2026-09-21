FROM postgres:18.6-alpine3.24

ENV TZ=Europe/Kyiv
ENV HOME=/home/${USERNAME}

ARG UID
ARG GID
ARG UMASK
ARG USERNAME

RUN apk add --no-cache \
    ca-certificates tzdata shadow

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
  && echo "${TZ}" > /etc/timezone

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
    /var/lib/postgresql/ \
    /var/run/postgresql/ \
    /var/log/postgresql/ \
  && chown -R ${UID}:${GID} \
    /var/lib/postgresql/ \
    /var/run/postgresql/ \
    /var/log/postgresql/ \
  && chmod -R 755 /var/lib/postgresql/ \
    /var/log/postgresql/ \
    /var/run/postgresql/

COPY --chown=${UID}:${GID} .docker/dev/postgresql/entrypoint.sh /usr/local/bin/custom-entrypoint.sh

RUN chmod +x /usr/local/bin/custom-entrypoint.sh

USER ${USERNAME}

WORKDIR /var/lib/postgresql

EXPOSE 5432

ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]

#CMD ["postgres", "-c", "config_file=/etc/postgresql/postgresql.conf"]
CMD ["postgres"]