<?php

namespace CM3_Lib\Action\Application\LocationCoord;

use CM3_Lib\models\application\locationcoord;
use CM3_Lib\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class Update
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param eventinfo $eventinfo The service
     */
    public function __construct(private Responder $responder, private locationcoord $locationcoord)
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $id): ResponseInterface
    {
        // Extract the form data from the request body
        $data = (array)$request->getParsedBody();
        $data['id'] = $id['id'];

        // Invoke the Domain with inputs and retain the result
        $data = $this->locationcoord->Delete($data);

        // Build the HTTP response
        return $this->responder
            ->withJson($response, $data);
    }
}
