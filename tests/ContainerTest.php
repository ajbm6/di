<?php

namespace League\Dic\Test;

use League\Dic\Container;
use League\Dic\Definition\Factory;
use League\Dic\Test\Asset\Baz;
use League\Dic\Test\Asset\BazStatic;
use League\Dic\Test\Asset\Foo;

/**
 * ContainerTest
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $configArray = [
        'League\Dic\Test\Asset\Foo' => [
            'class' => 'League\Dic\Test\Asset\Foo',
            'arguments' => ['League\Dic\Test\Asset\Bar'],
            'methods' => [
                'injectBaz' => ['League\Dic\Test\Asset\Baz']
            ]
        ],
        'League\Dic\Test\Asset\Bar' => [
            'definition' => 'League\Dic\Test\Asset\Bar',
            'arguments' => ['League\Dic\Test\Asset\Baz']
        ],
        'League\Dic\Test\Asset\Baz' => 'League\Dic\Test\Asset\Baz',
    ];

    public function testAutoResolvesNestedDependenciesWithAliasedInterface()
    {
        $c = new Container;

        $c->add('League\Dic\Test\Asset\BazInterface', 'League\Dic\Test\Asset\Baz');

        $foo = $c->get('League\Dic\Test\Asset\Foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->bar->baz);
        $this->assertInstanceOf('League\Dic\Test\Asset\BazInterface', $foo->bar->baz);
    }

    public function testInjectsArgumentsAndInvokesMethods()
    {
        $c = new Container;

        $c->add('League\Dic\Test\Asset\Bar')
          ->withArguments(['League\Dic\Test\Asset\Baz']);

        $c->add('League\Dic\Test\Asset\Baz');

        $c->add('League\Dic\Test\Asset\Foo')
          ->withArgument('League\Dic\Test\Asset\Bar')
          ->withMethodCall('injectBaz', ['League\Dic\Test\Asset\Baz']);

        $foo = $c->get('League\Dic\Test\Asset\Foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);
    }

    public function testInjectsRuntimeArgumentsAndInvokesMethods()
    {
        $c = new Container;

        $c->add('League\Dic\Test\Asset\Bar')
          ->withArguments(['League\Dic\Test\Asset\Baz']);

        $c->add('closure1', function ($bar) use ($c) {
            return $c->get('League\Dic\Test\Asset\Foo', [$bar]);
        })->withArgument('League\Dic\Test\Asset\Bar');

        $c->add('League\Dic\Test\Asset\Baz');

        $c->add('League\Dic\Test\Asset\Foo')
          ->withArgument('League\Dic\Test\Asset\Bar')
          ->withMethodCalls(['injectBaz' => ['League\Dic\Test\Asset\Baz']]);

        $runtimeBar = new \League\Dic\Test\Asset\Bar(
            new \League\Dic\Test\Asset\Baz
        );

        $foo = $c->get('League\Dic\Test\Asset\Foo', [$runtimeBar]);

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);

        $this->assertSame($foo->bar, $runtimeBar);

        $fooClosure = $c->get('closure1');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $fooClosure);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $fooClosure->bar);
    }

    public function testSingletonReturnsSameInstanceEverytime()
    {
        $c = new Container;

        $c->singleton('League\Dic\Test\Asset\Baz');

        $this->assertTrue($c->isSingleton('League\Dic\Test\Asset\Baz'));

        $baz1 = $c->get('League\Dic\Test\Asset\Baz');
        $baz2 = $c->get('League\Dic\Test\Asset\Baz');

        $this->assertTrue($c->isSingleton('League\Dic\Test\Asset\Baz'));
        $this->assertSame($baz1, $baz2);
    }

    public function testStoresAndInvokesClosure()
    {
        $c = new Container;

        $c->add('foo', function () {
            $foo = new \League\Dic\Test\Asset\Foo(
                new \League\Dic\Test\Asset\Bar(
                    new \League\Dic\Test\Asset\Baz
                )
            );

            $foo->injectBaz(new \League\Dic\Test\Asset\Baz);

            return $foo;
        });

        $foo = $c->get('foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);
    }

    public function testStoresAndInvokesClosureWithDefinedArguments()
    {
        $c = new Container;

        $baz = new \League\Dic\Test\Asset\Baz;
        $bar = new \League\Dic\Test\Asset\Bar($baz);

        $c->add('foo', function ($bar, $baz) {
            $foo = new \League\Dic\Test\Asset\Foo($bar);

            $foo->injectBaz($baz);

            return $foo;
        })->withArguments([$bar, $baz]);

        $foo = $c->get('foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);
    }

    public function testStoresAndReturnsArbitraryValues()
    {
        $baz1 = new \League\Dic\Test\Asset\Baz;
        $array1 = ['Phil', 'Bennett'];

        $c = new Container;

        $c->add('baz', $baz1);
        $baz2 = $c->get('baz');

        $c->add('array', $array1);
        $array2 = $c->get('array');

        $this->assertSame($baz1, $baz2);
        $this->assertSame($array1, $array2);
    }

    public function testReflectionOnNonClassThrowsException()
    {
        $this->setExpectedException('League\Dic\Exception\ReflectionException');

        (new Container)->get('FakeClass');
    }

    public function testReflectionOnClassWithNoConstructorCreatesDefinition()
    {
        $c = new Container;

        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $c->get('League\Dic\Test\Asset\Baz'));
    }

    public function testReflectionInjectsDefaultValue()
    {
        $c = new Container;

        $this->assertSame('Phil Bennett', $c->get('League\Dic\Test\Asset\FooWithDefaultArg')->name);
    }

    public function testReflectionThrowsExceptionForArgumentWithNoDefaultValue()
    {
        $this->setExpectedException('League\Dic\Exception\UnresolvableDependencyException');

        $c = new Container;

        $c->get('League\Dic\Test\Asset\FooWithNoDefaultArg');
    }

    public function testEnablingAndDisablingCachingWorksCorrectly()
    {
        $cache = $this->getMockBuilder('Orno\Cache\Cache')->disableOriginalConstructor()->getMock();

        $c = new Container($cache);

        $this->assertTrue($c->isCaching());

        $c->disableCaching();

        $this->assertFalse($c->isCaching());

        $c->enableCaching();

        $this->assertTrue($c->isCaching());
    }

    public function testContainerSetsCacheWhenAvailableAndEnabled()
    {
        $cache = $this->getMockBuilder('Orno\Cache\Cache')
                      ->setMethods(['get', 'set'])
                      ->disableOriginalConstructor()
                      ->getMock();

        $cache->expects($this->once())
              ->method('set')
              ->with($this->equalTo('orno::container::League\Dic\Test\Asset\Baz'));

        $cache->expects($this->once())
              ->method('get')
              ->with($this->equalTo('orno::container::League\Dic\Test\Asset\Baz'))
              ->will($this->returnValue(false));

        $c = new Container($cache);

        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $c->get('League\Dic\Test\Asset\Baz'));
    }

    public function testContainerGetsFromCacheWhenAvailableAndEnabled()
    {
        $cache = $this->getMockBuilder('Orno\Cache\Cache')
                      ->setMethods(['get', 'set'])
                      ->disableOriginalConstructor()
                      ->getMock();

        $definition = $this->getMockBuilder('League\Dic\Definition\ClassDefinition')
                           ->disableOriginalConstructor()
                           ->getMock();

        $definition->expects($this->any())
                   ->method('__invoke')
                   ->will($this->returnValue(new Asset\Baz));

        $definition = serialize($definition);

        $cache->expects($this->once())
              ->method('get')
              ->with($this->equalTo('orno::container::League\Dic\Test\Asset\Baz'))
              ->will($this->returnValue($definition));

        $c = new Container($cache);

        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $c->get('League\Dic\Test\Asset\Baz'));
    }

    public function testArrayAccessMapsToCorrectMethods()
    {
        $c = new Container;

        $c['League\Dic\Test\Asset\Baz'] = 'League\Dic\Test\Asset\Baz';

        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $c['League\Dic\Test\Asset\Baz']);

        $this->assertTrue(isset($c['League\Dic\Test\Asset\Baz']));

        unset($c['League\Dic\Test\Asset\Baz']);

        $this->assertFalse(isset($c['League\Dic\Test\Asset\Baz']));
    }

    public function testContainerAcceptsArrayWithKey()
    {
        $c = new Container(null, ['di' => $this->configArray]);

        $foo = $c->get('League\Dic\Test\Asset\Foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->bar->baz);
        $this->assertInstanceOf('League\Dic\Test\Asset\BazInterface', $foo->bar->baz);

        $baz = $c->get('League\Dic\Test\Asset\Baz');
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);
    }

    public function testContainerDoesntAcceptArrayWithoutKey()
    {
        $this->setExpectedException('RuntimeException');

        $c = new Container(null, $this->configArray);
    }

    public function testContainerAcceptsArrayAccess()
    {
        $config = $this->getMock('ArrayAccess', ['offsetGet', 'offsetSet', 'offsetUnset', 'offsetExists']);
        $config->expects($this->any())
               ->method('offsetGet')
               ->with($this->equalTo('di'))
               ->will($this->returnValue($this->configArray));

        $config->expects($this->any())
               ->method('offsetExists')
               ->with($this->equalTo('di'))
               ->will($this->returnValue(true));


        $c = new Container(null, $config);

        $foo = $c->get('League\Dic\Test\Asset\Foo');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->bar->baz);
        $this->assertInstanceOf('League\Dic\Test\Asset\BazInterface', $foo->bar->baz);

        $baz = $c->get('League\Dic\Test\Asset\Baz');
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->baz);
    }

    public function testContainerDoesntAcceptInvalidConfigType()
    {
        $this->setExpectedException('InvalidArgumentException');

        $c = new Container(null, new \stdClass());
    }

    public function testExtendThrowsExceptionWhenUnregisteredServiceIsGiven()
    {
        $this->setExpectedException('InvalidArgumentException');

        $c = new Container;
        $c->extend('does_not_exist');
    }

    public function testExtendsThrowsExceptionWhenModifyingAnExistingSingleton()
    {
        $this->setExpectedException('League\Dic\Exception\ServiceNotExtendableException');

        $c = new Container;
        $c->singleton('service', 'League\Dic\Test\Asset\Baz');
        $c->get('service');
        $c->extend('service');
    }

    public function testExtendReturnsDefinitionForModificationWhenCalledWithAValidService()
    {
        $c = new Container;
        $definition = $c->add('service', 'League\Dic\Test\Asset\Baz');
        $extend = $c->extend('service');

        $this->assertInstanceOf('League\Dic\Definition\DefinitionInterface', $extend);
        $this->assertSame($definition, $extend);
    }

    public function testCallExecutesAnonymousFunction()
    {
        $expected = 'foo';

        $c = new Container();
        $result = $c->call(function () use ($expected) {
            return $expected;
        });

        $this->assertSame($result, $expected);
    }

    public function testCallExecutesNamedFunction()
    {
        $method = '\League\Dic\Test\Asset\sayHi';

        $c = new Container();
        $returned = $c->call($method);
        $this->assertSame($returned, 'hi');
    }

    public function testCallExecutesCallableDefinedByArray()
    {
        $expected = 'qux';
        $baz = new BazStatic();

        $c = new Container();
        $returned = $c->call([$baz, 'qux']);

        $this->assertSame($returned, $expected);
    }

    public function testCallExecutesMethodsWithNamedParameters()
    {
        $expected = 'bar';

        $c = new Container;
        $returned = $c->call(function ($foo) {
            return $foo;
        }, ['foo' => $expected]);

        $this->assertSame($returned, $expected);
    }

    public function testCallExecutesStaticMethod()
    {
        $method = '\League\Dic\Test\Asset\BazStatic::baz';
        $expected = 'qux';

        $c = new Container();
        $returned = $c->call($method, ['foo' => $expected]);
        $this->assertSame($returned, $expected);
    }

    public function testCallResolvesTypeHintedArgument()
    {
        $expected = 'League\Dic\Test\Asset\Baz';

        $c = new Container;
        $returned = $c->call(function (Baz $baz) use ($expected) {
            return get_class($baz);
        });

        $this->assertSame($returned, $expected);
    }

    public function testCallMergesTypeHintedAndProvidedAttributes()
    {
        $expected = 'bar+League\Dic\Test\Asset\Baz';

        $c = new Container;
        $returned = $c->call(function ($foo, Baz $baz) use ($expected) {
            return $foo.'+'.get_class($baz);
        }, ['foo' => 'bar']);

        $this->assertSame($returned, $expected);
    }

    public function testCallFillsInDefaultParameterValues()
    {
        $expected = 'bar';

        $c = new Container;
        $returned = $c->call(function ($foo = 'bar') {
            return $foo;
        });

        $this->assertSame($returned, $expected);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCallThrowsRuntimeExceptionIfParameterResolutionFails()
    {
        $c = new Container;
        $c->call(function (array $foo) {
            return implode(',', $foo);
        });

        $this->assertFalse(true);
    }

    public function testCallDoesntThinksArrayTypeHintAreToBeResolvedByContainer()
    {
        $c = new Container();
        $returned = $c->call(function (array $foo = []) {
            return $foo;
        });

        $this->assertInternalType('array', $returned);
        $this->assertEmpty($returned);
    }

    public function testContainerResolvesRegisteredCallable()
    {
        $c = new Container;

        $c->add('League\Dic\Test\Asset\BazInterface', 'League\Dic\Test\Asset\Baz');

        $c->invokable('function', function (\League\Dic\Test\Asset\Foo $foo) {
            return $foo;
        })->withArgument('League\Dic\Test\Asset\Foo');

        $foo = $c->call('function');

        $this->assertInstanceOf('League\Dic\Test\Asset\Foo', $foo);
        $this->assertInstanceOf('League\Dic\Test\Asset\Bar', $foo->bar);
        $this->assertInstanceOf('League\Dic\Test\Asset\Baz', $foo->bar->baz);
        $this->assertInstanceOf('League\Dic\Test\Asset\BazInterface', $foo->bar->baz);
    }

    public function testCallThrowsExceptionWhenCannotResolveCallable()
    {
        $this->setExpectedException('RuntimeException');

        $c = new Container;

        $c->call('hello');
    }
}
