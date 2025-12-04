# BayLang for Laravel


## Install compiler

Install BayLang Compiler
```
cd ~
composer global require baylang/compiler
```


## Create project

Create laravel project:
```
composer create-project laravel/laravel project
```

Change folder:
```
cd project
```

Install BayLang laravel:
```
cd laravel
composer require baylang/laravel
```


## Include BayLang module

Add to bootstrap/providers.php:
```
<?php

return [
    App\Providers\AppServiceProvider::class,
    Runtime\Laravel\ServiceProvider::class,
];
```


## Init BayLang

Init BayLang:
```
php artisan baylang:init
```

Add to composer.json path to find app components:
```
"App\\": ["app/", "resources/php"],
```

Run:
```
composer dump-autoload
```


## Add route

Add main route in file routes/web.php
```
<?php

use Illuminate\Support\Facades\Route;
use Runtime\RenderContainer;

/* Main page */
Route::get('/', function () {
    
    /* Get render container */
    $container = app(RenderContainer::class);
    
    /* Setup page */
    $page = $container->layout->setPageModel("App.Components.Pages.IndexPage.IndexPageModel");
    $page->username = "User";
    
    /* Render app */
    return $container->renderApp();
});
```


## Compile

Compile project:
```
~/.composer/vendor/bin/baylang-php make_all
```

Launch watch changes:
```
~/.composer/vendor/bin/baylang-php watch
```