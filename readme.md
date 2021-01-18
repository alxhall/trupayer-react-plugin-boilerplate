## Docker
Run `docker-compose up -d` to launch WordPress. 
Run `docker-compose down` or `docker-compose stop` to shut down.

## WP-CLI
Run `docker-compose run --rm cli bash` to use wp-cli commands inside container. See [wp-cli.org](https://wp-cli.org/) for documentation.

## React plugin
Plugin is located in `trupayers-signup` and the React Application inside `trupayers-signup/trupayers-signup-react`. The plugin needs to be activated (from WordPress admin or with WP-CLI). The React Application is currently mounted on the DOM node `#page` on the WordPress front page (can/should probably be customized).

## Todo
Can't get the connection between port 3000 and 8000 on localhost to work together, so currently it's only possible to build the React application (not to run local server, live reloading etc.).