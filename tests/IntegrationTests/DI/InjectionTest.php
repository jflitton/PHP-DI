<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI;

use DI\ContainerBuilder;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Scope;
use DI\Container;
use IntegrationTests\DI\Fixtures\Class1;
use IntegrationTests\DI\Fixtures\Class2;
use IntegrationTests\DI\Fixtures\Implementation1;
use IntegrationTests\DI\Fixtures\LazyDependency;

/**
 * Test class for injection
 */
class InjectionTest extends \PHPUnit_Framework_TestCase
{
    const DEFINITION_REFLECTION = 0;
    const DEFINITION_ANNOTATIONS = 1;
    const DEFINITION_ARRAY = 2;
    const DEFINITION_PHP = 3;
    const DEFINITION_COMPILED_REFLECTION = 4;
    const DEFINITION_COMPILED_ANNOTATIONS = 5;
    const DEFINITION_COMPILED_ARRAY = 6;
    const DEFINITION_COMPILED_PHP = 7;

    public function setUp()
    {
        // Clear all files in directory
        foreach (glob(__DIR__ . '/compiled/*.php') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * PHPUnit data provider: generates container configurations for running the same tests
     * for each configuration possible
     * @return array
     */
    public static function containerProvider()
    {
        // Test with a container using reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(false);
        $containerReflection = $builder->build();
        $containerReflection->addDefinitions(array(
            'foo'                 => 'bar',
            'IntegrationTests\DI\Fixtures\Interface1' => \DI\object('IntegrationTests\DI\Fixtures\Implementation1'),
            'namedDependency'     => \DI\object('IntegrationTests\DI\Fixtures\Class2'),
            'IntegrationTests\DI\Fixtures\LazyDependency' => \DI\object()->lazy(),
            'alias'               => \DI\link('namedDependency'),
            'factory'             => \DI\factory(function () {
                return 42;
            }),
        ));

        // Test with a container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $containerAnnotations = $builder->build();
        $containerAnnotations->addDefinitions(array(
            'foo'             => 'bar',
            'IntegrationTests\DI\Fixtures\Interface1' => \DI\object('IntegrationTests\DI\Fixtures\Implementation1'),
            'namedDependency' => \DI\object('IntegrationTests\DI\Fixtures\Class2'),
            'alias'           => \DI\link('namedDependency'),
            'factory'         => \DI\factory(function () {
                return 42;
            }),
        ));

        // Test with a container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(new ArrayDefinitionSource(__DIR__ . '/Fixtures/definitions.php'));
        $containerArray = $builder->build();

        // Test with a container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $containerPHP = $builder->build();
        $containerPHP->set('foo', 'bar');
        $containerPHP->set(
            'IntegrationTests\DI\Fixtures\Class1',
            \DI\object()
                ->withScope(Scope::PROTOTYPE())
                ->withProperty('property1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->withProperty('property2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->withProperty('property3', \DI\link('namedDependency'))
                ->withProperty('property4', \DI\link('foo'))
                ->withProperty('property5', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->withConstructor(
                    \DI\link('IntegrationTests\DI\Fixtures\Class2'),
                    \DI\link('IntegrationTests\DI\Fixtures\Interface1'),
                    \DI\link('IntegrationTests\DI\Fixtures\LazyDependency')
                )
                ->withMethod('method1', \DI\link('IntegrationTests\DI\Fixtures\Class2'))
                ->withMethod('method2', \DI\link('IntegrationTests\DI\Fixtures\Interface1'))
                ->withMethod('method3', \DI\link('namedDependency'), \DI\link('foo'))
                ->withMethod('method4', \DI\link('IntegrationTests\DI\Fixtures\LazyDependency'))
        );
        $containerPHP->set('IntegrationTests\DI\Fixtures\Class2', \DI\object());
        $containerPHP->set('IntegrationTests\DI\Fixtures\Implementation1', \DI\object());
        $containerPHP->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            \DI\object('IntegrationTests\DI\Fixtures\Implementation1')
                ->withScope(Scope::SINGLETON())
        );
        $containerPHP->set('namedDependency', \DI\object('IntegrationTests\DI\Fixtures\Class2'));
        $containerPHP->set('IntegrationTests\DI\Fixtures\LazyDependency', \DI\object()->lazy());
        $containerPHP->set('alias', \DI\link('namedDependency'));
        $containerPHP->set('factory', \DI\factory(function () {
            return 42;
        }));

        // Test with a compiled container using reflection
        $builder = new ContainerBuilder();
        $builder->compileContainer(__DIR__ . '/compiled');
        $builder->useReflection(true);
        $builder->useAnnotations(false);
        $compiledContainerReflection = $builder->build();
        $compiledContainerReflection->addDefinitions(array(
            'foo'                 => 'bar',
            'IntegrationTests\DI\Fixtures\Class1'     => Entry::object()->withScope(Scope::PROTOTYPE()),
            'IntegrationTests\DI\Fixtures\Interface1' => Entry::object('IntegrationTests\DI\Fixtures\Implementation1'),
            'namedDependency'     => Entry::object('IntegrationTests\DI\Fixtures\Class2'),
            'IntegrationTests\DI\Fixtures\LazyDependency' => Entry::object()->lazy(),
            'alias'               => Entry::link('namedDependency'),
            'factory'             => Entry::factory(function () {
                return 42;
            }),
        ));

        // Test with a compiled container using annotations and reflection
        $builder = new ContainerBuilder();
        $builder->useReflection(true);
        $builder->useAnnotations(true);
        $compiledContainerAnnotations = $builder->build();
        $compiledContainerAnnotations->addDefinitions(array(
            'foo'             => 'bar',
            'IntegrationTests\DI\Fixtures\Interface1' => Entry::object('IntegrationTests\DI\Fixtures\Implementation1'),
            'namedDependency' => Entry::object('IntegrationTests\DI\Fixtures\Class2'),
            'alias'           => Entry::link('namedDependency'),
            'factory'             => Entry::factory(function () {
                return 42;
            }),
        ));

        // Test with a compiled container using array configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $compiledContainerArray = $builder->build();
        $array = require __DIR__ . '/Fixtures/definitions.php';
        $compiledContainerArray->addDefinitions($array);

        // Test with a compiled container using PHP configuration
        $builder = new ContainerBuilder();
        $builder->useReflection(false);
        $builder->useAnnotations(false);
        $compiledContainerPHP = $builder->build();
        $compiledContainerPHP->set('foo', 'bar');
        $compiledContainerPHP->set(
            'IntegrationTests\DI\Fixtures\Class1',
            Entry::object()
                ->withScope(Scope::PROTOTYPE())
                ->withProperty('property1', Entry::link('IntegrationTests\DI\Fixtures\Class2'))
                ->withProperty('property2', Entry::link('IntegrationTests\DI\Fixtures\Interface1'))
                ->withProperty('property3', Entry::link('namedDependency'))
                ->withProperty('property4', Entry::link('foo'))
                ->withProperty('property5', Entry::link('IntegrationTests\DI\Fixtures\LazyDependency'))
                ->withConstructor(
                    Entry::link('IntegrationTests\DI\Fixtures\Class2'),
                    Entry::link('IntegrationTests\DI\Fixtures\Interface1'),
                    Entry::link('IntegrationTests\DI\Fixtures\LazyDependency')
                )
                ->withMethod('method1', Entry::link('IntegrationTests\DI\Fixtures\Class2'))
                ->withMethod('method2', Entry::link('IntegrationTests\DI\Fixtures\Interface1'))
                ->withMethod('method3', Entry::link('namedDependency'), Entry::link('foo'))
                ->withMethod('method4', Entry::link('IntegrationTests\DI\Fixtures\LazyDependency'))
        );
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\Class2', Entry::object());
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\Implementation1', Entry::object());
        $compiledContainerPHP->set(
            'IntegrationTests\DI\Fixtures\Interface1',
            Entry::object('IntegrationTests\DI\Fixtures\Implementation1')
                ->withScope(Scope::SINGLETON())
        );
        $compiledContainerPHP->set('namedDependency', Entry::object('IntegrationTests\DI\Fixtures\Class2'));
        $compiledContainerPHP->set('IntegrationTests\DI\Fixtures\LazyDependency', Entry::object()->lazy());
        $compiledContainerPHP->set('alias', Entry::link('namedDependency'));
        $compiledContainerPHP->set('factory', Entry::factory(function () {
            return 42;
        }));

        return array(
            array(self::DEFINITION_REFLECTION, $containerReflection),
            array(self::DEFINITION_ANNOTATIONS, $containerAnnotations),
            array(self::DEFINITION_ARRAY, $containerArray),
            array(self::DEFINITION_PHP, $containerPHP),
            array(self::DEFINITION_COMPILED_REFLECTION, $compiledContainerReflection),
            array(self::DEFINITION_COMPILED_ANNOTATIONS, $compiledContainerAnnotations),
            array(self::DEFINITION_COMPILED_ARRAY, $compiledContainerArray),
            array(self::DEFINITION_COMPILED_PHP, $compiledContainerPHP),
        );
    }

    /**
     * @dataProvider containerProvider
     */
    public function testContainerHas($type, Container $container)
    {
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Class1'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Class2'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\Interface1'));
        $this->assertTrue($container->has('namedDependency'));
        $this->assertTrue($container->has('IntegrationTests\DI\Fixtures\LazyDependency'));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testConstructorInjection($type, Container $container)
    {
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->constructorParam1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->constructorParam2);

        // Test lazy injection (not possible using reflection)
        if ($type != self::DEFINITION_REFLECTION) {
            $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->constructorParam3);
            $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->constructorParam3);
            /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
            $proxy = $class1->constructorParam3;
            $this->assertFalse($proxy->isProxyInitialized());
            // Correct proxy resolution
            $this->assertTrue($proxy->getValue());
            $this->assertTrue($proxy->isProxyInitialized());
        }
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPropertyInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $proxy);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testPropertyInjectionExistingObject($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = new Class1(new Class2(), new Implementation1(), new LazyDependency());
        $container->injectOn($class1);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->property2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->property3);
        $this->assertEquals('bar', $class1->property4);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->property5;
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $proxy);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $proxy);
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMethodInjection($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = $container->get('IntegrationTests\DI\Fixtures\Class1');

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->method4Param1);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testMethodInjectionExistingObject($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        /** @var $class1 Class1 */
        $class1 = new Class1(new Class2(), new Implementation1(), new LazyDependency());
        $container->injectOn($class1);

        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method1Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Implementation1', $class1->method2Param1);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $class1->method3Param1);
        $this->assertEquals('bar', $class1->method3Param2);
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\LazyDependency', $class1->method4Param1);
        $this->assertInstanceOf('ProxyManager\Proxy\LazyLoadingInterface', $class1->method4Param1);
        // Lazy injection
        /** @var LazyDependency|\ProxyManager\Proxy\LazyLoadingInterface $proxy */
        $proxy = $class1->method4Param1;
        $this->assertFalse($proxy->isProxyInitialized());
        // Correct proxy resolution
        $this->assertTrue($proxy->getValue());
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider containerProvider
     */
    public function testScope($type, Container $container)
    {
        // Only constructor injection with reflection
        if ($type == self::DEFINITION_REFLECTION || $type == self::DEFINITION_COMPILED_REFLECTION) {
            return;
        }
        $class1_1 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $class1_2 = $container->get('IntegrationTests\DI\Fixtures\Class1');
        $this->assertNotSame($class1_1, $class1_2);
        $class2_1 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $class2_2 = $container->get('IntegrationTests\DI\Fixtures\Class2');
        $this->assertSame($class2_1, $class2_2);
        $class3_1 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $class3_2 = $container->get('IntegrationTests\DI\Fixtures\Interface1');
        $this->assertSame($class3_1, $class3_2);
    }

    /**
     * @dataProvider containerProvider
     */
    public function testFactory($type, Container $container)
    {
        $this->assertEquals(42, $container->get('factory'));
    }

    /**
     * @dataProvider containerProvider
     */
    public function testAlias($type, Container $container)
    {
        $this->assertInstanceOf('IntegrationTests\DI\Fixtures\Class2', $container->get('alias'));
    }
}
