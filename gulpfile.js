const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(mix => {
    mix.sass('app.scss').sass('login.scss').webpack('app.js');
    mix.copy('resources/assets/js/the-digits.js', 'public/js/the-digits.js');
    mix.copy('resources/assets/images/*.svg', 'public/images/');
    mix.copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyph*', 'public/fonts/bootstrap');
});
