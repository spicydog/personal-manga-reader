# Personal Manga Reader
A PHP application aims for advanced busy manga readers.

The application consists of manga crawler, that will download manga from another manga website and store on its files structure, and the website that works as the manga reader website.

This application was focused on lightweight to run on low power CPU devices such as RaspberryPi and Linux on mobile devices.

__DEMO:__ [https://www.spicydog.tk/manga](https://www.spicydog.tk/manga)

## Installation
1. Copy or rename `config.php.example` to `config.php`
1. Config constants in `config.php` to match your environment
1. Run `download.php` to start downloading  (you can use args to specific manga to download e.g.`php download.php manga-panda fairy-tail`)
1. Browse `index.php` on web browser to read manga
