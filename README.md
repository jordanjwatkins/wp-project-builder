#WP Project Builder

**Automates Local WordPress Project Setup**



I created this small PHP application to automate local setup and installation of WordPress projects for me and my team. 

- Checks for updates and downloads the most recent version of WordPress, plugins and Patch Roots (my starting theme based on the [Roots](http://roots.io/) theme)
- Creates project folders in specified directory
- Creates a database for the project
- Optionally installs WordPress with a few setting tweaks
- Optionally installs the most recent version of Patch Roots and brands it to match the current project
- Optionally installs selected plugins

**[Demo](http://jordanjwatkins.com/wp-project-builder/)**

##Install

1. Clone repository to a folder in your web root (I keep mine at `http://localhost/wp-project-builder`)
2. Open `config.php` and update `$config['db_user']` and `$config['db_pass']` to match your local phpMyAdmin credentials
3. Optionally modify other configuration options

##Configuration

- **default_project_root**: Default folder (relative to the WP Project Builder folder) for new projects (default: `../`)
- **db_user**: Local phpMyAdmin user (default: `root`)
- **db_pass**: Local phpMyAdmin password (default: no password)
- **wp_pass_suffix**: String added to project slug to create the WP install admin password (default: `123!`)
- **theme_author**: Author used in Patch Roots style.css (default: no author)
- **default_plugins**: Number of plugins automatically checked at the top of the plugins list (default: `3`)
- **plugins**: Array of plugins included on the plugins list (Plugin slugs must match the slug used by the WordPress.org plugin directory)

##Usage

1. In your browser, navigate to your WP Project Builder folder. (ex. `http://localhost/wp-project-builder`)
2. Enter a project name
3. Change any other options as desired
4. Click the 'Build' button
