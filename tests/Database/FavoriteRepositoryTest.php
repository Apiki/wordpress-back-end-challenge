<?php

namespace Apiki\FavoritePosts\Tests\Database;

use PHPUnit\Framework\TestCase;
use Apiki\FavoritePosts\Database\FavoriteRepository;

/**
 * Class FavoriteRepositoryTest
 *
 * Testes unitários para a classe FavoriteRepository.
 * Garante o funcionamento correto das querys SQL utilizando injeção do mock da classe wpdb.
 *
 * @package Apiki\FavoritePosts\Tests\Database
 */
class FavoriteRepositoryTest extends TestCase
{
    /**
     * Mock da classe wpdb.
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dbMock;

    /**
     * Instância do repositório a ser testado.
     *
     * @var FavoriteRepository
     */
    private $repository;

    /**
     * Configuração inicial executada antes de cada caso de teste.
     */
    protected function setUp(): void
    {
        // Criamos o mock da classe wpdb declarada no bootstrap.php
        $this->dbMock = $this->createMock(\wpdb::class);
        $this->dbMock->prefix = 'wp_';
        
        $this->repository = new FavoriteRepository($this->dbMock);
    }

    /**
     * Testa se adicionar um favorito que já existe retorna falso sem interagir com a gravação direta.
     */
    public function testAddAlreadyFavoritedReturnsFalse()
    {
        // Mocka a verificação interna is_favorited retornando true (1)
        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->willReturn('SELECT COUNT...');

        $this->dbMock->expects($this->once())
            ->method('get_var')
            ->willReturn(1);

        // O insert não deve ser invocado se já for favorito
        $this->dbMock->expects($this->never())
            ->method('insert');

        $result = $this->repository->add(1, 101);
        $this->assertFalse($result);
    }

    /**
     * Testa se adicionar um favorito com sucesso grava no banco e retorna verdadeiro.
     */
    public function testAddSuccessfulReturnsTrue()
    {
        // Mocka a verificação interna is_favorited retornando false (0)
        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->willReturn('SELECT COUNT...');

        $this->dbMock->expects($this->once())
            ->method('get_var')
            ->willReturn(0);

        // Espera a gravação correta com parâmetros mapeados
        $this->dbMock->expects($this->once())
            ->method('insert')
            ->with(
                'wp_apiki_favorites',
                $this->callback(function ($data) {
                    return $data['user_id'] === 1 && $data['post_id'] === 101;
                }),
                ['%d', '%d', '%s']
            )
            ->willReturn(1); // Sucesso de inserção retorna o número de linhas afetadas (1)

        $result = $this->repository->add(1, 101);
        $this->assertTrue($result);
    }

    /**
     * Testa se tentar remover um favorito inexistente retorna falso sem chamar exclusão.
     */
    public function testRemoveNotFavoritedReturnsFalse()
    {
        // Mocka is_favorited retornando falso (0)
        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->willReturn('SELECT COUNT...');

        $this->dbMock->expects($this->once())
            ->method('get_var')
            ->willReturn(0);

        // Delete não deve ser invocado
        $this->dbMock->expects($this->never())
            ->method('delete');

        $result = $this->repository->remove(1, 101);
        $this->assertFalse($result);
    }

    /**
     * Testa se remover um favorito existente remove com sucesso no banco de dados.
     */
    public function testRemoveSuccessfulReturnsTrue()
    {
        // Mocka is_favorited retornando verdadeiro (1)
        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->willReturn('SELECT COUNT...');

        $this->dbMock->expects($this->once())
            ->method('get_var')
            ->willReturn(1);

        // Espera chamada de delete correta
        $this->dbMock->expects($this->once())
            ->method('delete')
            ->with(
                'wp_apiki_favorites',
                ['user_id' => 1, 'post_id' => 101]
            )
            ->willReturn(1); // Linhas afetadas (1)

        $result = $this->repository->remove(1, 101);
        $this->assertTrue($result);
    }

    /**
     * Testa se a listagem de favoritos retorna os IDs convertidos em inteiros puros.
     */
    public function testGetFavoritesReturnsCleanIntegers()
    {
        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->willReturn('SELECT...');

        // Retorna string e inteiros misturados simulando o banco de dados
        $this->dbMock->expects($this->once())
            ->method('get_col')
            ->willReturn(['101', '102', 103]);

        $results = $this->repository->get_user_favorites(1, 10, 0);
        
        $this->assertEquals([101, 102, 103], $results);
        $this->assertIsInt($results[0]);
        $this->assertIsInt($results[1]);
        $this->assertIsInt($results[2]);
    }
}
