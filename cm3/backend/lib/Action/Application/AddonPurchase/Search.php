<?php

namespace CM3_Lib\Action\Application\AddonPurchase;

use CM3_Lib\models\application\addonpurchase;
use CM3_Lib\models\application\badge;
use CM3_Lib\models\application\badgetype;

use CM3_Lib\database\SearchTerm;
use CM3_Lib\database\View;
use CM3_Lib\database\Join;

use CM3_Lib\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Slim\Exception\HttpBadRequestException;

/**
 * Action.
 */
final class Search
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param eventinfo $eventinfo The service
     */
    public function __construct(private Responder $responder, private addonpurchase $addonpurchase, private badge $badge, private badgetype $badgetype)
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

        //Confirm permission to delete this badge applicant
        $badgeinfo = $this->badge->GetByID($params['application_id'], new View(
            array('id'),
            array(
                new Join($this->badgetype, array('id'=>'badge_type_id', new SearchTerm('event_id', $params['event_id'])))
            )
        ));

        if ($badgeinfo === false) {
            throw new HttpBadRequestException($request, 'Invalid badge specified');
        }


        $whereParts = array(
          new SearchTerm('application_id', $params['application_id'])
        );

        $order = array('id' => false);

        $page      = ($request->getQueryParams()['page']?? 0 > 0) ? $request->getQueryParams()['page'] : 1;
        $limit     = $request->getQueryParams()['itemsPerPage']?? -1; // Number of posts on one page
        $offset      = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        // Invoke the Domain with inputs and retain the result
        $data = $this->addonpurchase->Search(array(), $whereParts, $order, $limit, $offset);

        // Build the HTTP response
        return $this->responder
            ->withJson($response, $data);
    }
}
