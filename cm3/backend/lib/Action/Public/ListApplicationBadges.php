<?php

namespace CM3_Lib\Action\Public;

use CM3_Lib\database\SelectColumn;
use CM3_Lib\database\SearchTerm;
use CM3_Lib\database\View;
use CM3_Lib\database\Join;

use CM3_Lib\models\application\group;
use CM3_Lib\models\application\badgetype;
use CM3_Lib\models\application\submission;

use CM3_Lib\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class ListApplicationBadges
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param eventinfo $eventinfo The service
     */
    public function __construct(private Responder $responder, private group $group, private badgetype $badgetype, private submission $submission)
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
        $viewData = new View(
            array(
                  'id',
                  'display_order',
                  'name',
                  'description',
                  'rewards',
                  'max_applicant_count',
                  'max_assignment_count',
                  'base_price',
                  'base_applicant_count',
                  'base_assignment_count',
                  'price_per_applicant',
                  'price_per_assignment',
                  'max_prereg_discount',
                  'payable_onsite',
                  'max_total_applications',
                  'max_total_applicants',
                  'max_total_assignments',
                  'start_date',
                  'end_date',
                  'min_age',
                  'max_age',
                  'dates_available',
                  new SelectColumn('quantity_sold', EncapsulationFunction: 'ifnull(?,0)', Alias: 'quantity_sold', JoinedTableAlias: 'q')

              ),
            array(
              new Join(
                  $this->submission,
                  array(
                    'badge_type_id'=>'id',
                  ),
                  'LEFT',
                  'q',
                  array(
                    'badge_type_id',
                    new SelectColumn('id', true, 'count(?)', 'quantity_sold')
                ),
                  array(
                   new SearchTerm('payment_status', 'Cancelled', "<>"),
                   new SearchTerm('payment_status', 'Rejected', "<>"),
                 )
              ),
              new Join(
                  $this->group,
                  array('id'=>'group_id'),
                  alias: 'grp'
              )
            )
        );

        $whereParts = array(
                  new SearchTerm('event_id', $params['event_id'], JoinedTableAlias: 'grp'),
                  new SearchTerm('active', 1)
                );

        $order = array('display_order' => false);


        // Invoke the Domain with inputs and retain the result
        $data = $this->badgetype->Search($viewData, $whereParts, $order);

        // Build the HTTP response
        return $this->responder
            ->withJson($response, $data);
    }
}