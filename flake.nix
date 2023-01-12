{
  # Run `nix flake update` to update inputs
  inputs.nixpkgs.url = "github:NixOS/nixpkgs";
  inputs.flake-utils.url = "github:numtide/flake-utils";
  inputs.devshell.url = "github:numtide/devshell";

  outputs = { self, nixpkgs, flake-utils, devshell }: flake-utils.lib.eachDefaultSystem (system:
    let pkgs = import nixpkgs {
      inherit system;
      overlays = [ devshell.overlay ];
    };
    in
    {
      devShell = import ./devshell.nix { inherit pkgs; };
    }
  );
}
