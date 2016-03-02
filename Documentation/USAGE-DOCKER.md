[<-- Back to main section](../README.md)

# Usage of `ct docker:...`

## Docker creation

You can easily create new docker instances (from my or a custom docker boilerplate) also with code initialization
and Makefile running.

```bash
# Create new docker boilerplate into foobar directory
ct docker:create foobar

# Create and start new docker boilerplate into foobar directory
ct docker:create foobar --up

# Create new typo3 docker boilerplate
ct docker:create foobar --docker=typo3

# Create new custom docker boilerplate 
ct docker:create foobar --docker=git...

# Create new docker boilerplate with code repository
ct docker:create foobar --code=git...

# Create new docker boilerplate with code repository and makefile run
ct docker:create foobar --code=git... --make=build

# Create and start new docker boilerplate with code repository and makefile run
ct docker:create foobar --code=git... --make=build --up
```

## Docker startup

The `docker:up` command will search the `docker-compose.yml` in the current parent directory tree and
execute `docker-compose` from this directory - you don't have to change the current directory.

Also the previous docker instance will be shut down to avoid port conflicts.

```bash
# Startup docker-compose
ct docker:up
```

## Custom docker commands

As `docker:up` the `docker:compose` will search the `docker-compose.yml` and will execute your command
from this directory.

```bash
# Stop docker instance
ct docker:compose stop

# Show docker container status
ct docker:compose ps
```

Hint: You can use `alias dcc='ct docker:compose'` for this.

## Docker shell access

There are many ways to jump into docker containers:

```bash
# Jump into a root shell
ct docker:root 

# Jump into a root shell in mysql container
ct docker:root mysql

# Jump into a user shell (defined by CLI_USER as docker env)
ct docker:shell 

# Jump into a root user in mysql container (defined by CLI_USER as docker env)
ct docker:root mysql
```

## Docker command execution

```bash
# Execute command "ps" in "main" container
ct docker:exec ps 
```

## Docker cli execution

You can define a common CLI script entrypoint with the environment variable CLI_SCRIPT in your docker containers.
The environment variable will be read by `ct docker:cli` and will be executed - you don't have to jump
into your containers, you can start your CLI_SCRIPTs from the outside.

```bash
# Execute predefined cli command with argument "help" in "main" container
ct docker:cli help
```

## Docker debugging

If you want to debug a docker application (eg. your webpage inside docker) the `ct docker:sniff` provides you
a network sniffer set for various protocols (eg. http or mysql).

```bash
# Show basic http traffic
ct docker:sniff http 

# Show full http traffic
ct docker:sniff http --full

# Show mysql querys by using network sniffer
ct docker:sniff mysql 
```

## Docker cleanup
Docker currently doesn't cleanup orphaned images or volumes so you have to cleanup your system regularly to get free disk space

```bash
ct docker:cleanup
```

It's a shortcut for:

```bash
# Docker image cleanup:
docker images | grep "<none>" | awk "{print \$3}" | xargs --no-run-if-empty docker rmi -f

# Docker volume cleanup
docker pull martin/docker-cleanup-volumes
docker run -v /var/run/docker.sock:/var/run/docker.sock -v /var/lib/docker:/var/lib/docker --rm martin/docker-cleanup-volumes
```
