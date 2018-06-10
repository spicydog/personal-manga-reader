# Personal Manga Reader
A PHP application aims for advanced manga readers.

The application consists of manga reader website and crawlers, that download manga from another manga website and store on its file structure.

This application is focusing on the lightweight that aims to run on RaspberryPi and Linux on mobile devices.

__DEMO:__ [https://manga.spicydog.org](https://manga.spicydog.org) (Running on my Raspberry Pi Zero W)

## Installation
1. Copy or rename `config.php.example` to `config.php`
1. Adjust constants in `config.php` to match your environment
1. Run `download.php` to start downloading  (you can use args to specific manga to download e.g.`php download.php manga-panda one-piece`)
1. Browse `index.php` on web browser to read manga
