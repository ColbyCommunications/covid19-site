# WordPress template for Platform.sh

This project provides a starter kit for Colby College WordPress projects (forked from University of Missouri) hosted on Platform.sh. It is built using Composer, via the popular <a href="https://github.com/johnpbloch/wordpress">johnpbloch/wordpress</a> script.

## How to Use This Repo as a Template

1. Click "Use this Template" button
2. Create it in the Colby Communications organization under the naming convention `[sitename]-site`
3. Clone on local machine
4. Add [sitename] to the following files, replacing placeholder text:

-   composer.json
-   .platform.app.yaml
-   .lando.yaml
-   README.md

5. Create an upstream to the starter repo via `git remote add upstream https://github.com/ColbyCommunications/platformsh-wp-starter`

6. Link with Platform.sh:

-   Create blank project in Platform.sh console
-   Add Platform.sh repo as remote: `platform project:set-remote [project_id]`

## How to Navigate the Project

### Composer and Dependencies

All free wordpress plugins and themes are dependencies of the project and are pulled in via Composer and composer.json. Free plugins and themes are typically found on <a href="https://wpackagist.org/">WP Packagist</a>. The use of composer makes it easy to tie plugins and themes down to a specific version with composer's [versioning syntax](https://getcomposer.org/doc/articles/versions.md).

Premium plugins/themes need to be committed to the repository and put in the `web/wp-content` directory. When doing this, you'll also need to modify the .gitignore file to make sure you expose the new plugin/theme to git.

When no composer.lock is present, you can just run `composer install` to get all fresh dependencies. If a composer.lock is present, you'll need to run `composer update` to update currently installed dependecies. For example, when making changes to a Colby plugin or theme, it is common to then pull those in via a `composer update` command.

### Scripts

All scripts for the project are found in the `scripts` directory. Scripts are run at different times during development, build and deploy on Platform.sh. You can see how scripts are invoked by following the trail from `.platform.app.yaml` or `lando.yaml`.

### Lando Local Development

You'll need docker and lando installed in order to run a local version of the project on your machine. After those pre-reqs are install and after you `cd` into this project. You should be easily able to run `lando start` to start the local server and `lando stop` to stop it. You can say `no` to any prompts. If you change your lando yaml config anytime after you've set up the initial project, you'll need to run `lando rebuild -y` to rebuild with the new config.

### Setup

When setting up the site for the first time inside platform, the root user should always be `webmaster@colby.edu`. The password should be different than any used in the past. We keep track of these passwords in the Office LastPass.

### Helpful Commands

`platform db:dump` - dumps the database from the current Platform.sh environment and downloads it to the project folder  
`platform mount:download --mount="web/wp-content/uploads" --target="web/wp-content/uploads"` - downloads all media uploads from the current Platform.sh environment  
`platform environment:activate` - activates the current environment (mostly used for dev branches)  
`platform ssh` - ssh tunnels into current Platform.sh cloud container  
`platform sql < [dump].sql` - replaces current Platform.sh database data with a local dump file

## Change Log

### 2.1.0

-   adds github actions for interacting with Platform repos
-   adds support for satis.colby.edu
-   remove redis
-   upgrade node in Platform.sh CI to v16
-   move Platform.sh dependencies, WP CLI mostly, to composer
-   removes unneccesary .platform.template.yaml file
-   removes baseinstall
-   updates lando to PHP 7.4
-   removes disk.yaml and runtime.extensions.yaml in favor of putting those right in .platform.app.yaml
-   new format for .npmrc
-   simplify lando build - get rid of platform sync prompt
-   adds support for composer allowedPlugins
-   adds support for .env files + generation scripts
-   moves Platform CI to composer 2
-   adds WP Search with Algolia Plugin

### 2.0.1

-   patch for wrong wpgraphql version

### 2.0.0

-   update plugins: elementor-pro, jet plugins, ACF, gravityforms, yoast
