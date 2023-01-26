# M151

This project is a simple API for a bank. Users can create accounts and transfer money between them. They can also view
their full transaction history.

## Architecture

When a request is made to the API, it's handled by the `index.php` file. It loads all available routes from the
controllers in the `Controller` directory using reflection, and then, based on the request path and method, it calls the appropriate route handler.

In `thema1`, a custom ORM is used to interact with the database. It uses reflection to automatically discover the
database schema based on PHP attributes, and then it generates SQL queries based on the provided data.

## Setting up a development environment

1. Install Nix or NixOS: https://nixos.org/download.html
2. Enable flakes: https://nixos.wiki/wiki/Flakes#Enable_flakes
3. Enter dev shell
    ```sh
    nix develop
    ```
4. Start PHP server
    ```sh
    phpstart <thema1 | thema2>
    ```
5. Write a config file in `<thema1 | thema2>/config.php`, along the lines of:

    ```php
    <?php
    use App\Config;

    return new Config(
        jwtKey: '(long random string, e.g. `openssl rand -hex 128`)',
        dbHost: 'localhost',
        dbUser: 'root',
        dbPassword: 'password',
        dbName: 'thema1',
    );
    ```

## Reflection

I already had experience with PHP, so this project was pretty easy to make. I did, however, learn about reflection,
which I had never used in PHP before. While I learned a lot by writing my own ORM, it was pretty difficult, so in the
future I'll use an existing one. Mine works, but pre-existing ones are much more mature and have a lot more features.
