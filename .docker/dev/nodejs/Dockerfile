# Development Dockerfile for Nuxt.js Frontend
FROM node:25.6.1-slim

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kyiv

RUN apt-get update \
  && apt-get -y install \
    curl \
    ca-certificates \
    unzip \
    zip \
    tzdata \
  && rm -rf /var/lib/apt/lists/*

# set time zone in container
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
  && echo ${TZ} > /etc/timezone

# Import UID and GID from docker compose
ARG UID
ARG GID
ARG UMASK
ARG USERNAME

ENV HOME=/home/${USERNAME}

# Configure npm prefix for processes in the image and ensure PATH includes it
ENV NPM_CONFIG_PREFIX=${HOME}/.npm-global
ENV NPM_CONFIG_CACHE=${HOME}/.npm
ENV PATH="${NPM_CONFIG_PREFIX}/bin:${PATH}"

# Re-create symlinks exactly like in official node image
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
  && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

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

# Create npm global directory for www-data and set ownership
RUN mkdir -p \
    ${NPM_CONFIG_PREFIX}/ \
    ${NPM_CONFIG_CACHE}/ \
    /var/www/html \
  && chown -R ${UID}:${GID} \
    ${NPM_CONFIG_PREFIX}/ \
    ${NPM_CONFIG_CACHE}/ \
    /var/www/html \
  && chmod -R 755 \
    ${NPM_CONFIG_CACHE}/ \
    ${NPM_CONFIG_PREFIX}/ \
    /var/www/html

# Copy only package files to keep layer small and cacheable
COPY --chown=${UID}:${GID} httpdocs/frontend/package*.json /var/www/html

# Copy and set permissions for entrypoint script
COPY --chown=${UID}:${GID} .docker/dev/nodejs/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 3000

USER ${USERNAME}

#RUN npm ci

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]