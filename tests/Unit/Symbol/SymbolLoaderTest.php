<?php

declare(strict_types=1);

namespace Icanhazstring\Composer\Test\Unused\Unit\Symbol;

use Composer\Package\Package;
use Generator;
use Icanhazstring\Composer\Unused\Symbol\DependencySymbolLoader;
use Icanhazstring\Composer\Unused\Symbol\NamespaceSymbol;
use Icanhazstring\Composer\Unused\Symbol\Provider\FunctionConstantSymbolProvider;
use Icanhazstring\Composer\Unused\Symbol\SymbolInterface;
use Icanhazstring\Composer\Unused\Symbol\SymbolLoader;
use Icanhazstring\Composer\Unused\Symbol\SymbolLoaderInterface;
use PHPUnit\Framework\TestCase;

class SymbolLoaderTest extends TestCase
{
    protected function arrayAsGenerator(array $values): Generator
    {
        foreach ($values as $value) {
            yield $value;
        }
    }

    private function assertContainsSymbol(SymbolInterface $symbol, array $symbolHaystack): void
    {
        foreach ($symbolHaystack as $refSymbol) {
            if ($refSymbol->matches($symbol)) {
                return;
            }
        }

        $this->fail($symbol->getIdentifier() . ' not found in haystack');
    }

    /**
     * @test
     */
    public function itShouldMatchPsr0(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/');
        $package->setAutoload([
            'psr-0' => [
                'Test\\Namespace\\' => 'src'
            ]
        ]);

        $symbolLoader = new SymbolLoader();

        $fileSymbolLoader = $this->getMockBuilder(FunctionConstantSymbolProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileSymbolLoader
            ->method('provide')
            ->willReturn($this->arrayAsGenerator([]));

        $symbolLoader = new DependencySymbolLoader($fileSymbolLoader, $symbolLoader);
        $symbols = $symbolLoader->load($package);

        $symbolsArray = iterator_to_array($symbols);
        self::assertCount(1, $symbolsArray);
        $this->assertContainsSymbol(new NamespaceSymbol('Test\\Namespace\\'), $symbolsArray);
    }

    /**
     * @test
     */
    public function itShouldMatchPsr4(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/');
        $package->setAutoload([
            'psr-4' => [
                'Test\\Namespace\\' => 'src'
            ]
        ]);

        $symbolLoader = new SymbolLoader();

        $fileSymbolLoader = $this->getMockBuilder(FunctionConstantSymbolProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileSymbolLoader
            ->method('provide')
            ->willReturn($this->arrayAsGenerator([]));

        $symbolLoader = new DependencySymbolLoader($fileSymbolLoader, $symbolLoader);
        $symbols = $symbolLoader->load($package);

        $symbolsArray = iterator_to_array($symbols);
        self::assertCount(1, $symbolsArray);
        $this->assertContainsSymbol(new NamespaceSymbol('Test\\Namespace\\'), $symbolsArray);
    }
}
