<?php

namespace CM3_Lib\Action\AdminUser;

use CM3_Lib\database\SearchTerm;
use CM3_Lib\models\admin\user;
use CM3_Lib\models\contact;
use CM3_Lib\Responder\Responder;
use CM3_Lib\util\TokenGenerator;
use CM3_Lib\util\CurrentUserInfo;
use CM3_Lib\util\EventPermissions;
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
    public function __construct(
        private Responder $responder,
        private user $user,
        private contact $contact,
        private TokenGenerator $TokenGenerator,
        private CurrentUserInfo $CurrentUserInfo
    ) {
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

        // Invoke the Domain with inputs and retain the result
        $data = $this->user->GetByID($params['id'], [
            'contact_id',
            'username',
            'active',
            'adminOnly',
            'preferences',
            'permissions'
        ]);

        //Fetch the contact record as well

        $data['contact'] = $this->contact->GetByID($data['contact_id'], ['id',
            'uuid',
            'date_created',
            'date_modified',
            'allow_marketing',
            'email_address',
            'real_name',
            'phone_number',
            'address_1',
            'address_2',
            'city',
            'state',
            'zip_code',
            'country',
            'notes']);


        //Translate permissions
        $eperms = $this->TokenGenerator->decodePermissionsString($data['permissions']);
        if (isset($eperms->EventPerms[$this->CurrentUserInfo->GetEventId()])) {
            $data['permissions'] = $eperms->EventPerms[$this->CurrentUserInfo->GetEventId()]->getPermEnumeration();
        } else {
            //They don't have permissions in this event, send an empty one
            $data['permissions'] = new EventPermissions();
        }

        // Build the HTTP response
        return $this->responder
            ->withJson($response, $data);
    }
}
