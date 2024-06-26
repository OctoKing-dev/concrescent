<?php

namespace CM3_Lib\Action\Application\BadgeType;

use CM3_Lib\database\SearchTerm;
use CM3_Lib\models\application\badgetype;
use CM3_Lib\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
    public function __construct(private Responder $responder, private badgetype $badgetype)
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

        $whereParts = array(
            new SearchTerm('id', $params['id']),
            new SearchTerm('group_id', $request->getAttribute('group_id'))
        );

        // Invoke the Domain with inputs and retain the result
        $data = $this->badgetype->Search("*", $whereParts)[0];

        // Build the HTTP response
        return $this->responder
            ->withJson($response, $data);
    }
}
