# PHP SOCKS Proxy Server

Easily run a SOCKS proxy server within a Docker container that listens on all available IPv4 and IPv6 addresses, and by
default supports outbound connections using the same IP where the connection is accepted.

### Configurable environment variables

| VARIABLE   | DEFAULT | DETAILS             |
|:-----------|:--------|:--------------------|
| SOCKS_USR  |         | Authorized User     |
| SOCKS_PWD  |         | Authorized Password |
| SOCKS_PORT | 1080    | SOCKS Server Port   |

### Running inside a Docker container

```
SOCKS_USR= SOCKS_PWD= docker compose up -d 
```

### Stop and remove the Docker container, then clean up its image

```
docker compose down --remove-orphans --rmi all
```

### Running directly via command line

```
SOCKS_USR= SOCKS_PWD= bin/console app:socks-proxy 
```
