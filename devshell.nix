{ pkgs }:
with pkgs;
devshell.mkShell {
  name = "m151";

  commands = [
    {
      name = "phpstart";
      help = "starts php service";
      category = "php";
      command = ''
        if [ $# -ne 1 ]; then
          echo "usage: phpstart <thema1 | thema2>"
          exit 1
        fi
        proj=$1
        mkdir -p "$PRJ_DATA_DIR"
        cd $proj
        echo "starting php service"
        php -S 0.0.0.0:8080 src/index.php > "$PRJ_DATA_DIR/php.log" 2>&1 &
        echo $! > "$PRJ_DATA_DIR/php.pid"
        echo "php listening on http://localhost:8080"
      '';
    }
    {
      name = "phpstop";
      help = "stops php service";
      category = "php";
      command = ''
        echo "stopping php service"
        kill $(cat "$PRJ_DATA_DIR/php.pid")
      '';
    }
    {
      name = "php";
      category = "php";
      package = php82.buildEnv {
        extensions = ({ enabled, all }: enabled ++ (with all; [
          xdebug
        ]));
      };
    }
    {
      name = "composer";
      category = "php";
      package = php82Packages.composer;
    }
  ];

  devshell.startup = {
    setup_exit_hooks.text = ''
      stop_services() {
        [ -f "$PRJ_DATA_DIR/php.pid" ] && [ -d /proc/$(cat "$PRJ_DATA_DIR/php.pid") ] && phpstop
      }

      trap stop_services EXIT
    '';

    install_dependencies.text = ''
      echo "Installing dependencies..."
      cd thema1
      composer install
      cd ..
      cd thema2
      composer install
      cd ..
    '';
  };
}
