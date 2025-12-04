<?php

/*!
 *  BayLang Technology
 *
 *  (c) Copyright 2016-2025 "Ildar Bikmamatov" <support@bayrell.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Runtime\Laravel;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;


class InitCommand extends Command
{
	protected $signature = "baylang:init";
	protected $description = "Init BayLang project";
	
	
	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$this->info("Init BayLang");
		$this->newLine();
		$this->createProject();
		$this->createModule();
		$this->createCSS();
		$this->createIndexPage();
		$this->createIndexPageModel();
		$this->createModuleDescription();
		$this->publishAssets();
		$this->downloadVue();
	}
	
	
	/**
	 * Create project.json
	 */
	public function createProject()
	{
		$file_path = base_path("project.json");
		if (file_exists($file_path)) return;
		
		$this->info("Create project.json");
		$content = [
			"name" => "Laravel project",
			"description" => "Description",
			"license" => "MIT",
			"author" => "",
			"languages" => ["php", "es6"],
			"modules" => [
				[
					"src" => "./app",
					"type" => "lib"
				]
			],
			"assets" => [
				[
					"type" => "js",
					"dest" => "public/assets/app.js",
					"modules" => [
						"App"
					]
				]
			],
			"exclude" => []
		];
		$content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Create module
	 */
	public function createModule()
	{
		$file_path = base_path("app/module.json");
		if (file_exists($file_path)) return;
		
		$this->info("Create module.json");
		$content = [
			"name" => "App",
			"assets" => [
				"Components/Blocks/CSS.bay",
				"Components/Pages/IndexPage/IndexPage.bay",
				"Components/Pages/IndexPage/IndexPageModel.bay",
				"ModuleDescription.bay"
			],
			"src" => "./",
			"dest" => [
				"php" => "../resources/php",
				"es6" => "../resources/es6"
			],
			"allow" => [
				"\\.bay$"
			]
		];
		$content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Create CSS
	 */
	public function createCSS()
	{
		$file_path = base_path("app/Components/Blocks/CSS.bay");
		if (file_exists($file_path)) return;
		
		$file_dir = dirname($file_path);
		if (!is_dir($file_dir))
		{
			mkdir($file_dir, 0777, true);
		}
		
		$this->info("Create CSS");
		
		$content = <<<EOT
<class name="App.Components.Blocks.CSS">

<style global="true">
html, body{
	padding: 0;
	margin: 0;
}
</style>

</class>
EOT;
		
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Create index page
	 */
	public function createIndexPage()
	{
		$file_path = base_path("app/Components/Pages/IndexPage/IndexPage.bay");
		if (file_exists($file_path)) return;
		
		$file_dir = dirname($file_path);
		if (!is_dir($file_dir))
		{
			mkdir($file_dir, 0777, true);
		}
		
		$this->info("Create IndexPage");
		
		$content = <<<EOT
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
EOT;
		
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Create index page model
	 */
	public function createIndexPageModel()
	{
		$file_path = base_path("app/Components/Pages/IndexPage/IndexPageModel.bay");
		if (file_exists($file_path)) return;
		
		$file_dir = dirname($file_path);
		if (!is_dir($file_dir))
		{
			mkdir($file_dir, 0777, true);
		}
		
		$this->info("Create IndexPageModel");
		
		$content = <<<EOT
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
EOT;
		
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Create module description
	 */
	public function createModuleDescription()
	{
		$file_path = base_path("app/ModuleDescription.bay");
		if (file_exists($file_path)) return;
		
		$this->info("Create ModuleDescription");
		
		$content = <<<EOT
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
EOT;
		
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Download Vue
	 */
	public function downloadVue()
	{
		$file_path = base_path("public/assets/core/vue.runtime.global.prod.js");
		if (file_exists($file_path)) return;
		
		$file_dir = dirname($file_path);
		if (!is_dir($file_dir))
		{
			mkdir($file_dir, 0777, true);
		}
		
		$source_url = 'https://unpkg.com/vue@3/dist/vue.runtime.global.prod.js';
		$this->info("Download Vue from {$source_url}");
		
		$content = @file_get_contents($source_url);
		file_put_contents($file_path, $content);
	}
	
	
	/**
	 * Publish assets
	 */
	public function publishAssets()
	{
		$this->info("Publish assets");
		Artisan::call("vendor:publish", [
			"--provider" => ServiceProvider::class,
			"--force" => true,
		]);
	}
	
	
	/**
	 * Change composer
	 */
	public function changeComposer()
	{
		$file_path = base_path("composer.json");
		if (!file_exists($file_path)) return;
		
		$content = file_get_contents($file_path);
		$content = json_decode($content, true);
		if (!$content) return;
		
		if (!isset($content["autoload"])) return;
		if (!isset($content["autoload"]["psr-4"])) return;
		if (!isset($content["autoload"]["psr-4"]["App\\"]))
		{
			$content["autoload"]["psr-4"]["App\\"] = [];
		}
		
		$updated = false;
		if (!in_array("resources/php", $content["autoload"]["psr-4"]["App\\"]))
		{
			$content["autoload"]["psr-4"]["App\\"][] = "resources/php";
			$updated = true;
		}
		
		if ($updated)
		{
			$this->info("Update composer.json");
			$data = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			file_put_contents($file_path, $data);
			
			$process = Process::fromShellCommandline('composer dump-autoload');
			$process->run(function ($type, $buffer)
			{
				echo($buffer);
			});
		}
	}
}