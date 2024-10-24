# Module GEODIS pour [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Fonctionnement

Ce module permet la récupération et l'actualisation à intervalles réguliers des informations concernant les expéditions
effectuées via Geodis (Url de tracking, date de livraison, détails sur l'expédition)

Il suffit lors de la création de l'expédition chez Geodis (via leur portail https://espace-client.geodis.com) de mettre en référence l
de saisir dans Référence #1 ou Référence #2 la ref de l'expédition type SHyymm-xxxx

Ensuite, à condition que les tâches planifiées soient correctement appelées 
(voir Outils d'adminstration->travaux planifiés)
l'appel au service Geodis s'effectuera automatiquement toutes les heures
<!--
![Screenshot geodis](img/screenshot_geodis.png?raw=true "Geodis"){imgmd}
-->

D'autres modules sont disponibles sur [Dolistore.com](https://www.dolistore.com).

## Translations

Translations can be completed manually by editing files in the module directories under `langs`.

<!--
This module contains also a sample configuration for Transifex, under the hidden directory [.tx](.tx), so it is possible to manage translation using this service.

For more information, see the [translator's documentation](https://wiki.dolibarr.org/index.php/Translator_documentation).

There is a [Transifex project](https://transifex.com/projects/p/dolibarr-module-template) for this module.
-->


## Installation

Prerequisites: You must have Dolibarr ERP & CRM software installed. You can download it from [Dolistore.org](https://www.dolibarr.org).
You can also get a ready-to-use instance in the cloud from https://saas.dolibarr.org


### From the ZIP file and GUI interface

If the module is a ready-to-deploy zip file, so with a name `module_xxx-version.zip` (e.g., when downloading it from a marketplace like [Dolistore](https://www.dolistore.com)),
go to menu `Home> Setup> Modules> Deploy external module` and upload the zip file.

Note: If this screen tells you that there is no "custom" directory, check that your setup is correct:

<!--

- In your Dolibarr installation directory, edit the `htdocs/conf/conf.php` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading `//`) and assign the proper value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```
-->

<!--

### From a GIT repository

Clone the repository in `$dolibarr_main_document_root_alt/geodis`

```shell
cd ....../custom
git clone git@github.com:gitlogin/geodis.git geodis
```

-->

### Final steps

Using your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup"> "Modules"
  - You should now be able to find and enable the module



## Licenses

### Main code

GPLv3 or (at your option) any later version. See file COPYING for more information.

### Documentation

All texts and readme's are licensed under [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).
