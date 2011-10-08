<?php
	class Cache {
		private static $cache;
		public static function init() {
			global $config;
			
			switch($config['cache']['enabled']) {
				case 'memcached':
					self::$cache = new Memcached();
					self::$cache->addServers($config['cache']['memcached']);
					break;
			}
		}
		public static function get($key) {
			global $config, $debug;
			
			if(!self::$cache)
				self::init();
			
			$data = false;
			switch($config['cache']['enabled']) {
				case 'memcached':
					$data = self::$cache->get($key);
					break;
				case 'apc':
					$data = apc_fetch($key);
					break;
				case 'xcache':
					$data = xcache_get($key);
					break;
			}
			
			// debug
			if($data && $config['debug']) {
				$debug['cached'][] = $key;
			}
			
			return $data;
		}
		public static function set($key, $value, $expires = false) {
			global $config;
			
			if(!self::$cache)
				self::init();
			
			if(!$expires)
				$expires = $config['cache']['timeout'];
			
			switch($config['cache']['enabled']) {
				case 'memcached':
					self::$cache->set($key, $value, $expires);
					break;
				case 'apc':
					apc_store($key, $value, $expires);
					break;
				case 'xcache':
					xcache_set($key, $value, $expires);
					break;
			}			
		}
		public static function delete($key) {
			global $config;
			
			if(!self::$cache)
				self::init();
			
			switch($config['cache']['enabled']) {
				case 'memcached':
					self::$cache->delete($key);
					break;
				case 'apc':
					apc_delete($key);
					break;
				case 'xcache':
					xcache_unset($key);
					break;
			}
		}
		public static function flush() {
			global $config;
			
			if(!self::$cache)
				self::init();
			
			switch($config['cache']['enabled']) {
				case 'memcached':
					return self::$cache->flush();
				case 'apc':
					return apc_clear_cache();
			}
			
			return false;
		}
	}
	