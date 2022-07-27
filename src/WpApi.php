<?php

namespace rk\LaravelWpApi;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use GuzzleHttp\Exception\RequestException;

class WpApi
{

    use Macroable;

    /**
     * Guzzle client
     *
     * @var Client
     */
    protected $client;

    /**
     * WP-WPI endpoint URL
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Auth headers
     *
     * @var mixed
     */
    protected $auth;

    /**
     * Constructor
     *
     * @param string $endpoint
     * @param Client $client
     * @param mixed $auth
     */
    public function __construct(string $endpoint, Client $client, $auth = null)
    {
        // Ensure there's a trailing slash to the endpoint as there will be
        // a path appended to it. Prevents user error for a tiny cost.
        if (!Str::endsWith($endpoint, '/')) {
            $endpoint .= '/';
        }

        $this->endpoint = $endpoint;
        $this->client   = $client;
        $this->auth     = $auth;
    }

    /**
     * Get all posts
     *
     * @param int $page
     * @param array $params
     * @return array
     */
    public function posts(int $page = null, array $params = []): array
    {
        return $this->get('posts', ['page' => $page], $params);
    }

    /**
     * Get all pages
     *
     * @param int $page
     * @param array $params
     * @return array
     */
    public function pages(int $page = null, array $params = []): array
    {
        return $this->get('posts', ['type' => 'page', 'page' => $page], $params);
    }

    /**
     * Get post by id
     *
     * @param int $id
     * @return array
     */
    public function postId(int $id): array
    {
        return $this->get("posts/$id");
    }

    /**
     * Get post by slug
     *
     * @param string $slug
     * @return array
     */
    public function post(string $slug): array
    {
        return $this->get('posts', ['filter' => ['name' => $slug]]);
    }

    /**
     * Get page by slug
     *
     * @param string $slug
     * @return array
     */
    public function page(string $slug): array
    {
        return $this->get('posts', ['type' => 'page', 'filter' => ['name' => $slug]]);
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function categories(): array
    {
        return $this->get('categories');
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function tags(): array
    {
        return $this->get('taxonomies/post_tag/terms');
    }

    /**
     * Get posts from category
     *
     * @param string $slug
     * @param int $page
     * @return array
     */
    public function categoryPosts(string $slug, int $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['category_name' => $slug]]);
    }

    /**
     * Get posts by author
     *
     * @param string $name
     * @param int $page
     * @return array
     */
    public function authorPosts(string $name, int $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['author_name' => $name]]);
    }

    /**
     * Get posts tagged with tag
     *
     * @param string $tags
     * @param int $page
     * @return array
     */
    public function tagPosts(string $tags, int $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['tag' => $tags]]);
    }

    /**
     * Search posts
     *
     * @param string $query
     * @param int $page
     * @return array
     */
    public function search(string $query, int $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['s' => $query]]);
    }

    /**
     * Get posts by date
     *
     * @param int $year
     * @param int $month
     * @param int $page
     * @return array
     */
    public function archive(int $year, int $month, int $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['year' => $year, 'monthnum' => $month]]);
    }

    /**
     * Get data from the API
     *
     * @param string $method
     * @param array $query
     * @param array $params
     * @return array
     */
    public function get(string $method, array $query = [], array $params = []): array
    {
        try {
            $params['query'] = $query;

            if ($this->auth) {
                $params['auth'] = $this->auth;
            }

            $response = $this->client->get($this->endpoint . $method, $params);

            $return = [
                'results' => json_decode((string)$response->getBody(), true, JSON_THROW_ON_ERROR),
                'total'   => $response->getHeaderLine('X-WP-Total'),
                'pages'   => $response->getHeaderLine('X-WP-TotalPages'),
            ];
        } catch (RequestException $e) {
            $error['message'] = $e->getMessage();

            if ($e->getResponse()) {
                $error['code'] = $e->getResponse()->getStatusCode();
            }

            $return = [
                'error'   => $error,
                'results' => [],
                'total'   => 0,
                'pages'   => 0,
            ];
        }

        return $return;
    }
}
