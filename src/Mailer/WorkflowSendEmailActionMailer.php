<?php

namespace App\Mailer;

use App\Entity\SendEmailAction;
use App\Entity\User;

/**
 * Class WorkflowSendEmailActionMailer
 * @package App\Mailer
 */
class WorkflowSendEmailActionMailer extends AbstractMailer
{

    public function send(SendEmailAction $sendEmailAction) {

        $message = (new \Swift_Message($sendEmailAction->getSubject()))
            ->setFrom($this->siteFromEmail)
            ->setTo($sendEmailAction->getToAddresses())
            ->setBody(
                $this->templating->render(
                    'email/workflow_send_email_action.html.twig',
                    ['body' => $sendEmailAction->getBody()]
                ),
                'text/html'
            );

        $n = $this->mailer->send($message);
    }
}