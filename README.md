# BayLang for Laravel

Install BayLang Compiler
```
cd ~
composer global require baylang/compiler
```

Install BayLang laravel:
```
cd laravel
composer require baylang/laravel
```

Create project.json
```
{
    "name": "Laravel project",
    "description": "Description",
    "license": "MIT",
    "author": "",
    "languages": ["php", "es6"],
    "modules": [
        {
            "src": "./app",
            "type": "lib"
        }
    ],
    "assets": [
        {
            "type": "js",
            "dest": "public/assets/app.js",
            "modules": [
                "App"
            ]
        }
    ],
    "exclude": []
}
```

Create app/module.json
```
{
	"name": "App",
	"assets": [
		"Components/Blocks/CSS.bay",
		"Components/Pages/IndexPage/IndexPage.bay",
		"Components/Pages/IndexPage/IndexPageModel.bay",
		"ModuleDescription.bay"
	],
	"src": "./",
	"dest": {
		"php": "../resources/php",
		"es6": "../resources/es6"
	},
	"allow": [
		"\\.bay$"
	]
}
```

Add to composer.json path to find app components:
```
"App\\": ["app/", "resources/php"],
```

Create app/Components/Blocks/CSS.bay:
```
<class name="App.Components.Blocks.CSS">

<style global="true">
html, body{
    padding: 0;
    margin: 0;
}
</style>

</class>
```

Create app/Components/Pages/IndexPage/IndexPage.bay:
```
<class name="App.Components.Pages.IndexPage.IndexPage">

<use name="Runtime.Widget.Button" component="true" />

<style>
.index_page{
	text-align: center;
	padding-top: 100px;
}
</style>

<template>
	<div class="index_page">
		<div>Hello {{ this.model.username }}!</div>
		<Button @event:click="this.onClick()">Click</Button>
	</div>
</template>

<script>

void onClick()
{
	this.model.setUserName(this.model.username ~ "!");
}

</script>

</class>
```

Create app/Components/Pages/IndexPage/IndexPageModel.bay:
```
namespace App.Components.Pages.IndexPage;

use Runtime.BaseModel;
use App.Components.Pages.IndexPage.IndexPage;


class IndexPageModel extends BaseModel
{
	string component = classof IndexPage;
	string username = "User";
	
	
	/**
	 * Set user name
	 */
	void setUserName(string value)
	{
		this.username = value;
	}
}
```

Create app/ModuleDescription.bay
```
namespace App;

use Runtime.Entity.Hook;
use Runtime.Web.Hooks.Components;
use Runtime.Web.Hooks.SetupLayout;


class ModuleDescription
{
	/**
	 * Returns module name
	 * @return string
	 */
	pure string getModuleName() => "App";
	
	
	/**
	 * Returns module name
	 * @return string
	 */
	pure string getModuleVersion() => "0.0.1";
	
	
	/**
	 * Returns required modules
	 * @return Dict<string>
	 */
	pure Dict<string> requiredModules() =>
	{
		"Runtime.Web": "*",
		"Runtime.Widget": "*",
	};
	
	
	/**
	 * Returns enities
	 */
	pure Collection<Dict> entities() =>
	[
		Components::hook([
			"App.Components.Blocks.CSS",
		]),
		SetupLayout::hook({
			"default": "Runtime.BaseLayout",
		}),
	];
}
```

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

Download vue:
```
cd public/assets/core
wget https://unpkg.com/vue@3/dist/vue.runtime.global.prod.js
```

Publish assets:
```
php artisan vendor:publish
```

Compile project:
```
baylang-php make_all
```