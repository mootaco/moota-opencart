# moota-opencart

## Development
Run:

    ./link.sh <TARGET_DIR>
  
where `TARGET_DIR` is the root directory  
of your OpenCart installation.

e.g.:  

Opencart is in: `~/Sites/localhost/opencart`  
and there is this three folder in there: `admin`, `catalog`, `system`  

Run:

    ./link.sh ~/Sites/localhost/opencart

## Generate ocmod zip bundle (`mootapay.ocmod.zip`)

Run:

    ./bundle.sh

This script wil do the following (sequentially):

- Run `composer update`
- Create folder: `mootapay.ocmod` and cd into that
- Create folder: `upload` and cd into that
- Copy `admin`, `catalog`, and `system` from plugin's root dir into `upload`
- Changedir into plugin's root dir
- Deletes `mootapay.ocmod.zip`, if it already exists
- Creates `mootapay.ocmod.zip` in plugin's root dit
- Removes: `mootapay.ocmod/`

## Installation
- Download mootapay.ocmod.zip from the repo
- Upload it to opencart's extension installation page
