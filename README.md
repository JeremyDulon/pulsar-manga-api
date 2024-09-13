# Pulsar Manga

## INSTALL

### Local
- Setup your .env.local file
- Add local.pulsar.fr to /etc/hosts
- Connect to the web container
- Run `composer install`
- Run `php bin/console doctrine:schema:update --force`

### Prod

## ROADMAP

### Infra
- [X] Docker local

### Chapters
- [X] Next chapter
- [ ] No update read mode

### Users
- [ ] Register

### Favorites
- [X] User Chapter details (last read and last available)

### Admin
- [X] Add User
- [X] Add manga to import
- [X] EasyAdmin 3.x

### Import
- [X] Panther 1.x
- [ ] Autoupdate parameter

### Platforms
- [ ] MangaNelo
