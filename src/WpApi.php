<?php

namespace mam4dali\LaravelWpApi;

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
     * WP-WPI endpoint Main URL
     *
     * @var string
     */
    protected $endpointMain;

    /**
     * Auth headers
     *
     * @var mixed
     */
    protected $auth;
    /**
     * Auth Method
     *  basic or bearer
     * @var mixed
     */
    protected $authMethod = 'basic';

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
        $this->endpointMain = explode('/wp-json', $this->endpoint)[0]. '/wp-json/';
        $this->client   = $client;
        $this->auth     = $auth;
    }
    /**
     * Set And Enable Auth With JWT Token
     * @param string $token
     */
    public function SetJwtToken($token)
    {
        $this->authMethod = 'jwt';
        $this->auth = $token;
    }

    /**
     * JWT Token Validate
     *
     * @param string $token
     * @return array
     */
    public function jwtTokenValidate($token)
    {
        try {
            $url =  $this->endpointMain. 'jwt-auth/v1/token/validate';
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
            ]);
            $body = json_decode($response->getBody()->getContents());
            return [
                'error'   => null,
                'status' => ($body->code == 'jwt_auth_valid_token'),
            ];
        } catch (RequestException $e) {
            return [
                'error'   => $e->getMessage(),
                'status' => false,
            ];
        }
    }

    /**
     * JSON Web Token (JWT) Token Generate
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function jwtTokenGenerate($username, $password){
        try {
            $url = $this->endpointMain . 'jwt-auth/v1/token';
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'body' => json_encode($data),
            ]);
            $body = json_decode($response->getBody()->getContents());
            if(empty($body->token))
                throw new \Exception("Token is empty");
            return [
                'error'   => null,
                'status' => true,
                'token' => $body->token,
            ];
        } catch (RequestException $e) {
            $error['message'] = $e->getMessage();

            if ($e->getResponse()) {
                $error['code'] = $e->getResponse()->getStatusCode();
                $error['content'] = $e->getResponse()->getBody()->getContents();
            }
            return [
                'error'   => $error,
                'status' => false,
                'token' => null,
            ];
        } catch (\Exception $e) {
            return [
                'error'   => $e->getMessage(),
                'status' => false,
                'token' => null,
            ];
        }
    }

    /**
     * Get all posts
     *
     * @param int $page
     * @param array $params
     * @param int $per_page
     * @param array $url_params
     * @return array
     */
    public function posts(int $page = null, array $params = [], int $per_page = 10, array $url_params = []): array
    {
        return $this->get('posts', array_merge($url_params,['page' => $page, 'per_page' =>$per_page]), $params);
    }

    /**
     * Get all pages
     *
     * @param int $page
     * @param array $params
     * @param array $query
     * @param int $per_page
     * @return array
     */
    public function pages(int $page = 1, array $params = [], array $query = [], int $per_page = 10): array
    {
        return $this->get('pages', array_merge(['per_page' => $per_page, 'page' => $page], $query), $params);
    }

    /**
     * Get post by id
     *
     * @param int $id
     * @param array $url_params
     * @return array
     */
    public function postId(int $id, array $url_params = []): array
    {
        return $this->get("posts/$id", $url_params);
    }

    /**
     * Get post by ids
     *
     * @param array $post_ids
     * @param array $url_params
     * @return array
     */
    public function postIds(array $post_ids, array $url_params = []) : array{
        return $this->get('posts', array_merge($url_params,['include' => implode(',', $post_ids), 'orderby' => 'include']));
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
     * Legacy Search posts
     *
     * @param string $query
     * @param int $page
     * @param array $url_params
     * @return array
     */
    public function filterPosts(string $query, int $page = 1, int $per_page = 10, array $url_params = [])
    {
        return $this->get('posts', array_merge($url_params,['page' => $page, 'per_page' => $per_page, 'search' => $query]));
    }

    /**
     * Search posts
     *
     * @param string $query
     * @param int $page
     * @param int $per_page
     * @param string $type
     * @return array [array results,int total, int pages]
     */
    public function search(string $query, int $page = 1, int $per_page = 10, string $type = 'post')
    {
        return $this->get('search', ['page' => $page, 'per_page' => $per_page, 'search' => $query, 'type' => $type]);
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
                if($this->authMethod == 'jwt'){
                    $params['headers']['Authorization'] = 'Bearer ' . $this->auth;
                } else {
                    $params['auth'] = $this->auth;
                }
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
                $error['content'] = $e->getResponse()->getBody()->getContents();
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
