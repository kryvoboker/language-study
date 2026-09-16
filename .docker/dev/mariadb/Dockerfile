FROM mariadb:12.3.3

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kyiv

RUN apt-get update \
  && apt-get -y install tzdata \
  && rm -rf /var/lib/apt/lists/*

# set time zone in container
RUN ln -fs /usr/share/zoneinfo/Europe/Kyiv /etc/localtime

# Import UID and GID from docker compose
ARG UID
ARG GID
ARG UMASK
ARG USERNAME

ENV HOME=/home/${USERNAME}

# Create mysql group and user with specific UID/GID if they don't match
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

# Ensure proper ownership of MariaDB directories
RUN chown -R ${UID}:${GID} /var/lib/mysql/ /var/run/mysqld/ /etc/mysql/

# Copy custom entrypoint
COPY --chown=${UID}:${GID} .docker/dev/mariadb/entrypoint.sh /usr/local/bin/custom-entrypoint.sh

RUN chmod +x /usr/local/bin/custom-entrypoint.sh

WORKDIR /var/www/html

USER ${USERNAME}

ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]

CMD ["mariadbd"]