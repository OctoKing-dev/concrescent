<?php

namespace CM3_Lib\Action\Filestore;

use CM3_Lib\util\BaseIntEncoder;

use CM3_Lib\database\SearchTerm;
use CM3_Lib\models\filestore;
use CM3_Lib\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

/**
 * Action.
 */
final class Read
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param eventinfo $eventinfo The service
     */
    public function __construct(private Responder $responder, private filestore $filestore)
    {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $params): ResponseInterface
    {
        // Extract the form data from the request body
        $data = (array)$request->getParsedBody();
        //TODO: Actually do something with submitted data. Also, provide some sane defaults

        $result = $this->filestore->GetByID($params['id'], array(
            'id',
            'event_id',
            'context',
            'name',
            'meta',
            'visible',
            'mimetype',
            'date_created',
            'date_modified',
        ));
        if ($result === false) {
            throw new HttpNotFoundException($request);
        }
        if ($result['event_id'] != $request->getAttribute('event_id')) {
            throw new HttpBadRequestException($request, 'Filestore item does not belong to the current event!');
        }
        if (isset($result['data'])) {
            $result['data'] = base64_encode($result['data']);
        }

        //Generate a link for this file for public consumption
        $result['link'] = '/public/' . $request->getAttribute('event_id') . '/file/'
        . BaseIntEncoder::encode($params['id']) . '/' . $result['name'];

        // Build the HTTP response
        return $this->responder
             ->withJson($response, $result);
    }
}
