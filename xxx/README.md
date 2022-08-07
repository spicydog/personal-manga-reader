# Personal Manga Reader
A PHP application aims for advanced manga readers.

The application consists of manga reader website and crawlers, that download manga from another manga website and store on its file structure.

This application is focusing on the lightweight that aims to run on Raspberry Pi and Linux on mobile devices.

__DEMO:__ [https://manga.spicydog.org](https://manga.spicydog.org) (Running on my Raspberry Pi 400 in Portainer at home and go online with Cloudflare Argo Tunnel)

__DOCKER REPO:__ [https://hub.docker.com/repository/docker/spicydog/personal-manga-reader](https://hub.docker.com/repository/docker/spicydog/personal-manga-reader)

__GITHUB REPO:__ [https://github.com/spicydog/personal-manga-reader](https://github.com/spicydog/personal-manga-reader)

## Installation
1. Copy or rename `config.php.example` to `config.php`
1. Adjust constants in `config.php` to match your environment
1. Run `downloader.php` to start downloading  (you can set ENV variables to specify manga to download e.g.`CRAWLER=manga-panda;NAME=one-piece;`)
1. Browse `index.php` on web browser to read manga
