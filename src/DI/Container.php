<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Annotations\AnnotationException;
use DI\MetadataReader\DefaultMetadataReader;
use DI\Annotations\Inject;
use DI\Annotations\Value;
use DI\Injector\DependencyInjector;
use DI\Injector\ValueInjector;
use DI\MetadataReader\MetadataReader;
use DI\Proxy\Proxy;

/**
 * Container
 *
 * This class uses the resettable Singleton pattern (resettable for the tests).
 */
class Container
{

	private static $singletonInstance = null;

	/**
	 * Map of instances
	 * @var array object[name]
	 */
	private $beanMap = array();

	/**
	 * Array of the values to inject with the Value annotation
	 * @var array value[key]
	 */
	private $valueMap = array();

	/**
	 * Map of instances/class names to use for abstract classes and interfaces
	 * @var array array(interface => implementation)
	 */
	private $classAliases = array();

	/**
	 * @var DependencyInjector
	 */
	private $dependencyInjector;

	/**
	 * @var ValueInjector
	 */
	private $valueInjector;

	/**
	 * @var MetadataReader
	 */
	private $metadataReader;

	/**
	 * Returns an instance of the class (Singleton design pattern)
	 * @return \DI\Container
	 */
	public static function getInstance() {
		if (self::$singletonInstance == null) {
			self::$singletonInstance = new self();
		}
		return self::$singletonInstance;
	}

	/**
	 * Reset the singleton instance, for the tests only
	 */
	public static function reset() {
		self::$singletonInstance = null;
	}

	/**
	 * Protected constructor because of singleton
	 */
	protected function __construct() {
		$this->dependencyInjector = new DependencyInjector();
		$this->valueInjector = new ValueInjector();
	}

	/**
	 * Returns an instance by its name
	 *
	 * @param string $name Can be a bean name or a class name
	 * @param bool   $useProxy If true, returns a proxy class of the instance
	 * 						   if it is not already loaded
	 * @return mixed Instance
	 * @throws BeanNotFoundException
	 */
	public function get($name, $useProxy = false) {
		if (array_key_exists($name, $this->beanMap)) {
			return $this->beanMap[$name];
		}
		$className = $name;
		// Try to find a mapping for the implementation to use
		if (array_key_exists($name, $this->classAliases)) {
			$className = $this->classAliases[$className];
		}
		// Try to find the bean
		if (array_key_exists($className, $this->beanMap)) {
			return $this->beanMap[$className];
		}
		// Instance not found, use the factory to create it
		if (class_exists($className)) {
			if (!$useProxy) {
				$this->beanMap[$className] = $this->getNewInstance($className);
				return $this->beanMap[$className];
			} else {
				// Return a proxy class
				return $this->getProxy($className);
			}
		}
		throw new BeanNotFoundException("No bean or class named '$name' was found");
	}

	/**
	 * Define a bean in the container
	 *
	 * @param string $name Bean name or class name to be used with Inject annotation
	 * @param object $instance Instance
	 */
	public function set($name, $instance) {
		$this->beanMap[$name] = $instance;
	}

	/**
	 * Resolve the dependencies of the object
	 *
	 * @param mixed $object Object in which to resolve dependencies
	 * @throws Annotations\AnnotationException
	 * @throws DependencyException
	 */
	public function resolveDependencies($object) {
		if (is_null($object)) {
			throw new DependencyException("null given, object instance expected");
		}
		// Get the class metadata
		$annotations = $this->getMetadataReader()->getClassMetadata(get_class($object));
		// Process annotations
		foreach ($annotations as $propertyName => $annotation) {
			$property = new \ReflectionProperty(get_class($object), $propertyName);
			if ($annotation instanceof Inject) {
				$this->dependencyInjector->inject($object, $property, $annotation, $this);
			}
			if ($annotation instanceof Value) {
				$this->valueInjector->inject($object, $property, $annotation, $this->valueMap);
			}
		}
	}

	/**
	 * Read and applies the configuration found in the file
	 *
	 * Doesn't reset the configuration to default before importing the file.
	 * @param string $configurationFile the php-di configuration file
	 * @throws \Exception
	 * @throws DependencyException
	 */
	public function addConfigurationFile($configurationFile) {
		if (!(file_exists($configurationFile) && is_readable($configurationFile))) {
			throw new \Exception("Configuration file $configurationFile doesn't exist or is not readable");
		}
		// Read ini file
		$data = parse_ini_file($configurationFile);
		// Implementation map
		if (isset($data['di.types.map']) && is_array($data['di.types.map'])) {
			$mappings = $data['di.types.map'];
			foreach ($mappings as $contract => $implementation) {
				$this->setClassAlias($contract, $implementation);
			}
		}
		// Values map
		if (isset($data['di.values']) && is_array($data['di.values'])) {
			$this->valueMap = array_merge($this->valueMap, $data['di.values']);
		}
	}

	/**
	 * Map the implementation to use for an abstract class or interface
	 * @param string $contractType the abstract class or interface name
	 * @param string $implementationType Class name of the implementation
	 */
	public function setClassAlias($contractType, $implementationType) {
		$this->classAliases[$contractType] = $implementationType;
	}

	/**
	 * @return MetadataReader The metadata reader
	 */
	public function getMetadataReader() {
		if ($this->metadataReader == null) {
			$this->metadataReader = new DefaultMetadataReader();
		}
		return $this->metadataReader;
	}

	/**
	 * @param MetadataReader $metadataReader The metadata reader
	 */
	public function setMetadataReader(MetadataReader $metadataReader) {
		$this->metadataReader = $metadataReader;
	}

	/**
	 * Returns a proxy class
	 * @param string $classname
	 * @return \DI\Proxy\Proxy Proxy instance
	 */
	private function getProxy($classname) {
		$container = $this;
		return new Proxy(function() use ($container, $classname) {
			// Create the instance and add it to the container
			$instance = new $classname();
			$container->resolveDependencies($instance);
			$container->set($classname, $instance);
			return $instance;
		});
	}

	/**
	 * Create a new instance of the class
	 * @param string $classname Class to instantiate
	 * @return string the instance
	 */
	private function getNewInstance($classname) {
		$instance = new $classname();
		Container::getInstance()->resolveDependencies($instance);
		return $instance;
	}

	private final function __clone() {}

}
