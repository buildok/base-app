<?php
namespace buildok\base;

use buildok\base\exceptions\BaseHttpException;
use buildok\helpers\ArrayWrapper;

/**
 * Controller class
 */
class Controller
{
    /**
     * Supported request methods
     */
    const METHODS = ['GET', 'POST'];

    /**
     * Supported content type
     */
    const CONTENT_TYPE = 'application/json';

    /**
     * Object of request data
     * @var ArrayWrapper
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], self::METHODS)) {
            throw new BaseHttpException('Unsupported request method:' . $income['method'], 400);
        }

        $income['headers'] = $this->getRequestHeaders();
        $income['method'] = $_SERVER['REQUEST_METHOD'];
        if (!empty($_GET)) {
            $income['get'] = $_GET;
        }
        if (!empty($_POST)) {
            $income['post'] = $_POST;

            if ($income['headers']['Content-Type'] == self::CONTENT_TYPE) {
                $body = json_decode(file_get_contents('php://input'));

                if (is_null($body)) {
                    throw new BaseHttpException('Bad content format', 400);
                }

                $income['body'] = $body;
            }
        }

        $this->request = new ArrayWrapper($income);

        $this->request->headers['isAjax'] = array_key_exists('X-Requested-With', $this->request->headers)
            && (strcasecmp($this->request->headers['X-Requested-With'], 'XmlHttpRequest') == 0);
    }

    /**
     * Returns request headers
     * @return array
     */
    private function getRequestHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}