<?php

namespace Apiki\FavoritePosts\Tests;

use PHPUnit\Framework\TestCase;
use Apiki\FavoritePosts\Plugin;

/**
 * Class PluginTest
 *
 * Testes unitários para a classe Plugin principal (bootstrap).
 * Garante a integridade do padrão Singleton e o correto registro de hooks.
 *
 * @package Apiki\FavoritePosts\Tests
 */
class PluginTest extends TestCase
{
    /**
     * Limpa as ações mockadas antes de rodar o teste.
     */
    protected function setUp(): void
    {
        $GLOBALS['wp_mock_actions'] = [];
    }

    /**
     * Valida se a classe principal segue estritamente o padrão Singleton.
     */
    public function testPluginIsSingleton()
    {
        $instance1 = Plugin::get_instance();
        $instance2 = Plugin::get_instance();

        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(Plugin::class, $instance1);
    }

    /**
     * Valida se a inicialização do plugin registra a ação rest_api_init do WordPress corretamente.
     */
    public function testRunRegistersRestApiInitAction()
    {
        $plugin = Plugin::get_instance();
        $plugin->run();

        $this->assertArrayHasKey('rest_api_init', $GLOBALS['wp_mock_actions']);
        $this->assertCount(1, $GLOBALS['wp_mock_actions']['rest_api_init']);
        
        $callback = $GLOBALS['wp_mock_actions']['rest_api_init'][0];
        $this->assertEquals([$plugin, 'register_rest_routes'], $callback);
    }
}
