<?php

namespace ClassKit\Tests;

use SplFileInfo;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;

final class CreateSvelteComponentTest extends TestCase
{
    private string $projectRoot;

    /**
     * @return array{int, string}
     */
    private function runGenerator(string $input): array
    {
        $command = [PHP_BINARY, dirname(__DIR__) . '/bin/create-svelte-component'];
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $this->projectRoot,
        );

        self::assertIsResource($process);

        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $output];
    }

    public function testItCreatesTemplatedComponentAndRegistersItsPath(): void
    {
        [$exitCode, $output] = $this->runGenerator("store\nsingle_pages/product_details\n");

        self::assertSame(0, $exitCode, $output);
        self::assertStringContainsString('Component ProductDetails (product-details) created', $output);

        $componentPath = $this->projectRoot
            . '/public/packages/store/single_pages/product_details/view';

        self::assertSame(
            "<svelte:options customElement={{ tag: 'product-details', shadow: 'none' }} />\n\n"
            . "<script lang=\"ts\"></script>\n\n<!-- component code here -->\n",
            file_get_contents($componentPath . '/ProductDetails.svelte'),
        );
        self::assertSame("<product-details></product-details>\n", file_get_contents($componentPath . '/view.php'));
        self::assertSame("import '../ProductDetails.svelte';\n", file_get_contents($componentPath . '/ts/index.ts'));
        self::assertSame(
            ['./public/packages/store/single_pages/product_details/view'],
            json_decode(
                (string) file_get_contents($this->projectRoot . '/svelte-components.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    public function testItAddsComponentsToAnExistingRegistry(): void
    {
        $registryPath = $this->projectRoot . '/svelte-components.json';
        file_put_contents($registryPath, "[\n    \"public/application/blocks/first\"\n]\n");

        [$exitCode, $output] = $this->runGenerator("\nblocks/second_component\n");

        self::assertSame(0, $exitCode, $output);
        self::assertFileExists($this->projectRoot . '/public/application/blocks/second_component/SecondComponent.svelte');
        self::assertSame(
            [
                'public/application/blocks/first',
                './public/application/blocks/second_component',
            ],
            json_decode((string) file_get_contents($registryPath), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function setUp(): void
    {
        $this->projectRoot = sys_get_temp_dir() . '/classkit-svelte-test-' . bin2hex(random_bytes(8));

        $applicationPath = $this->projectRoot . '/public/application';
        $classKitPath = $this->projectRoot . '/public/packages/class_kit/resources/_component';

        if (
            !mkdir($applicationPath, 0755, true)
            || !mkdir($this->projectRoot . '/public/packages/store', 0755, true)
            || !mkdir($classKitPath, 0755, true)
        ) {
            self::fail('Unable to create the Svelte component test project.');
        }

        $templatePath = dirname(__DIR__) . '/resources/_component';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($templatePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            self::assertInstanceOf(SplFileInfo::class, $item);

            $relativePath = substr($item->getPathname(), strlen($templatePath) + 1);
            $destination = $classKitPath . '/' . $relativePath;

            if ($item->isDir()) {
                if (!mkdir($destination, 0755, true)) {
                    self::fail("Unable to create template directory: {$destination}");
                }
                continue;
            }

            if (!copy($item->getPathname(), $destination)) {
                self::fail("Unable to copy template file: {$destination}");
            }
        }
    }

    protected function tearDown(): void
    {
        if (!isset($this->projectRoot) || !is_dir($this->projectRoot)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($this->projectRoot);
    }
}
