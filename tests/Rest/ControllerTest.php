<?php

namespace Apiki\FavoritePosts\Tests\Rest;

use PHPUnit\Framework\TestCase;
use Apiki\FavoritePosts\Rest\Controller;
use Apiki\FavoritePosts\Database\FavoriteRepository;

/**
 * Class ControllerTest
 *
 * Testes unitários para o controlador REST (Controller).
 * Valida roteamento virtual, sanitização, checagem de autorização e comportamento de erros HTTP.
 *
 * @package Apiki\FavoritePosts\Tests\Rest
 */
class ControllerTest extends TestCase
{
    /**
     * Mock do repositório de favoritos.
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $repositoryMock;

    /**
     * Controlador a ser testado.
     *
     * @var Controller
     */
    private $controller;

    /**
     * Configuração padrão reiniciando mocks globais.
     */
    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(FavoriteRepository::class);
        $this->controller = new Controller($this->repositoryMock);

        // Restaura estados globais de mock padrão definidos em tests/bootstrap.php
        $GLOBALS['wp_mock_logged_in'] = true;
        $GLOBALS['wp_mock_current_user_id'] = 1;
        $GLOBALS['wp_mock_posts'] = [];
        $GLOBALS['wp_mock_post_type_viewable'] = true;
    }

    /**
     * Valida que usuários não autenticados recebem erro 401 Unauthorized.
     */
    public function testCheckPermissionReturnsErrorWhenLoggedOut()
    {
        $GLOBALS['wp_mock_logged_in'] = false;
        
        $request = new \WP_REST_Request();
        $result = $this->controller->check_permission($request);

        $this->assertInstanceOf(\WP_Error::class, $result);
        $this->assertEquals('rest_forbidden', $result->get_error_code());
        $this->assertEquals(401, $result->get_error_data()['status']);
    }

    /**
     * Valida que usuários autenticados têm permissão concedida.
     */
    public function testCheckPermissionAllowsLoggedInUser()
    {
        $GLOBALS['wp_mock_logged_in'] = true;

        $request = new \WP_REST_Request();
        $result = $this->controller->check_permission($request);

        $this->assertTrue($result);
    }

    /**
     * Testa a rota GET para obter a lista de favoritos com seus respectivos cabeçalhos de paginação.
     */
    public function testGetItemsReturnsFavoritesWithPaginationHeaders()
    {
        $request = new \WP_REST_Request('GET', [
            'page'     => 2,
            'per_page' => 5
        ]);

        // Esperamos que pegue os favoritos com paginação correta (user=1, limit=5, offset=5)
        $this->repositoryMock->expects($this->once())
            ->method('get_user_favorites')
            ->with(1, 5, 5)
            ->willReturn([10, 11, 12]);

        $this->repositoryMock->expects($this->once())
            ->method('count_user_favorites')
            ->with(1)
            ->willReturn(13); // Total de favoritos no banco é 13

        $response = $this->controller->get_items($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $response);
        $this->assertEquals([10, 11, 12], $response->get_data());
        
        // Valida cabeçalhos de paginação HTTP
        $headers = $response->get_headers();
        $this->assertEquals(13, $headers['X-WP-Total']);
        $this->assertEquals(3, $headers['X-WP-TotalPages']); // 13 posts / 5 por pág. = 3 páginas
    }

    /**
     * Valida que tentar favoritar um post inexistente retorna 404 Not Found.
     */
    public function testCreateItemReturns404IfPostDoesNotExist()
    {
        $request = new \WP_REST_Request('POST', ['post_id' => 999]);
        
        // Simula que post 999 não existe no banco WordPress
        $GLOBALS['wp_mock_posts'] = [];

        $response = $this->controller->create_item($request);

        $this->assertInstanceOf(\WP_Error::class, $response);
        $this->assertEquals('apiki_post_not_found', $response->get_error_code());
        $this->assertEquals(404, $response->get_error_data()['status']);
    }

    /**
     * Valida que tentar favoritar rascunhos ou posts não públicos gera erro 400 Bad Request.
     */
    public function testCreateItemReturns400IfPostIsNotPublishOrNotPublic()
    {
        $request = new \WP_REST_Request('POST', ['post_id' => 201]);

        // Simula um post rascunho
        $draftPost = new \WP_Post([
            'ID'          => 201,
            'post_status' => 'draft',
            'post_type'   => 'post'
        ]);

        $GLOBALS['wp_mock_posts'][201] = $draftPost;

        $response = $this->controller->create_item($request);

        $this->assertInstanceOf(\WP_Error::class, $response);
        $this->assertEquals('apiki_invalid_post', $response->get_error_code());
        $this->assertEquals(400, $response->get_error_data()['status']);
    }

    /**
     * Valida favoritação com sucesso de post público e publicado retornando HTTP status 201.
     */
    public function testCreateItemSucceedsAndReturns201()
    {
        $request = new \WP_REST_Request('POST', ['post_id' => 101]);

        $validPost = new \WP_Post([
            'ID'          => 101,
            'post_status' => 'publish',
            'post_type'   => 'post'
        ]);
        $GLOBALS['wp_mock_posts'][101] = $validPost;

        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->with(1, 101)
            ->willReturn(true);

        $response = $this->controller->create_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $response);
        $this->assertEquals(201, $response->get_status());
        
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertEquals(101, $data['post_id']);
    }

    /**
     * Valida que tentar favoritar um post já favoritado anteriormente gera erro 400 Bad Request.
     */
    public function testCreateItemReturns400IfAlreadyFavorited()
    {
        $request = new \WP_REST_Request('POST', ['post_id' => 101]);

        $validPost = new \WP_Post([
            'ID'          => 101,
            'post_status' => 'publish',
            'post_type'   => 'post'
        ]);
        $GLOBALS['wp_mock_posts'][101] = $validPost;

        // Adição retorna falso
        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->willReturn(false);

        // O controller checa se é porque já existe
        $this->repositoryMock->expects($this->once())
            ->method('is_favorited')
            ->with(1, 101)
            ->willReturn(true);

        $response = $this->controller->create_item($request);

        $this->assertInstanceOf(\WP_Error::class, $response);
        $this->assertEquals('apiki_already_favorited', $response->get_error_code());
        $this->assertEquals(400, $response->get_error_data()['status']);
    }

    /**
     * Valida remoção com sucesso de favorito retornando status 200.
     */
    public function testDeleteItemSucceedsAndReturns200()
    {
        $request = new \WP_REST_Request('DELETE', ['post_id' => 101]);

        $this->repositoryMock->expects($this->once())
            ->method('remove')
            ->with(1, 101)
            ->willReturn(true);

        $response = $this->controller->delete_item($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertEquals(101, $data['post_id']);
    }

    /**
     * Valida que tentar desfavoritar um post que não estava nos favoritos retorna 404 Not Found.
     */
    public function testDeleteItemReturns404IfNotFavorited()
    {
        $request = new \WP_REST_Request('DELETE', ['post_id' => 101]);

        $this->repositoryMock->expects($this->once())
            ->method('remove')
            ->with(1, 101)
            ->willReturn(false); // remove falha porque não era favorito

        $response = $this->controller->delete_item($request);

        $this->assertInstanceOf(\WP_Error::class, $response);
        $this->assertEquals('apiki_not_favorited', $response->get_error_code());
        $this->assertEquals(404, $response->get_error_data()['status']);
    }
}
