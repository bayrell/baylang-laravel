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

use Runtime\Context;
use Runtime\RenderContainer;
use Runtime\Map;
use Runtime\Method;
use Runtime\Vector;
use Runtime\VirtualDom;
use Runtime\Entity\Provider;
use Runtime\Hooks\RuntimeHook;
use Runtime\Web\Hooks\AppHook;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;


class ServiceProvider extends BaseServiceProvider
{
	/**
	 * Register services
	 */
	public function register()
	{
		/* Global context */
		$this->app->singleton(Context::class, function ($app){
			
			/* Get params */
			$params = new Map([
				"base_path" => base_path(),
				"modules" => new Vector(
					"App", "Runtime.Web", "Runtime.Widget"
				),
			]);
			$params = Context::initParams($params);
			
			/* Create context */
			$context = new Context($params);
			\Runtime\rtl::setContext($context);
			
			/* Init context */
			$context->init($params);
			
			/* Returns context */
			return $context;
		});
		
		/* Render container */
		$this->app->singleton(RenderContainer::class, function ($app){
			$container = new \Runtime\Web\RenderContainer();
			$context = $app->get(Context::class);
			$context->hook(RuntimeHook::CREATE_CONTAINER, new Map([
				"container" => $container,
			]));
			return $container;
		});
	}
	
	
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Event::listen(RouteMatched::class, [$this, "onRouteMatched"]);
		Event::listen(RequestHandled::class, [$this, "onRequest"]);
		
		/* Get context */
		$context = $this->app->get(Context::class);
		$hook = $context->provider("hook");
		
		/* Add hook */
		$hook->register(RuntimeHook::LAYOUT_FOOTER, new Method($this, "onFooter"));
		
		/* Start context */
		$context->start();
		
		/* Register assets */
		$this->registerPublishes();
		
		/* Register commands */
		$this->commands([
			InitCommand::class,
		]);
	}
	
	
	/**
	 * Register assets
	 */
	public function registerPublishes()
	{
		$class = new \ReflectionClass(\Runtime\ModuleDescription::class);
		$packagePath = $class->getFileName();
		$packagePath = dirname($packagePath);
		$packageAssetsPath = realpath($packagePath . '/../../assets');
		
		if (!$packageAssetsPath) return;
		$this->publishes([$packageAssetsPath => public_path("assets/core")], "runtime-core");
	}
	
	
	/**
	 * On request
	 */
	public function onRequest($event)
	{
		$container = $this->app->get(RenderContainer::class);
		$container->request = $event->request;
	}
	
	
	/**
	 * On route matched
	 */
	public function onRouteMatched($event)
	{
		$container = $this->app->get(RenderContainer::class);
		$container->route = new \Runtime\Web\RouteModel(new Map([
			"uri" => $event->route->uri(),
			"name" => $event->route->getName(),
		]));
		$container->createlayout("default");
		
		$context = $this->app->get(Context::class);
		$context->hook(AppHook::ROUTE_BEFORE, new Map([
			"container" => $container,
		]));
	}
	
	
	/**
	 * On footer
	 */
	public function onFooter($params)
	{
		$components = $params->get("components");
		
		$version = \Runtime\ModuleDescription::getModuleVersion();
		$assets = new Vector(
			asset("assets/core/vue.runtime.global.prod.js?_=" . $version),
			asset("assets/core/runtime.js?_=" . $version),
			asset("assets/app.js"),
		);
		$items = $assets->map(function($path){
			$vdom = new VirtualDom();
			$vdom->name = "script";
			$vdom->attrs = new Map([
				"src" => $path,
			]);
			return $vdom;
		});
		
		$components->appendItems($items);
	}
}