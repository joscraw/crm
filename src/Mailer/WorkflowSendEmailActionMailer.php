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

    public function send($subject, $toAddresses, $body) {
        $message = (new \Swift_Message($subject))
            ->setFrom($this->siteFromEmail)
            ->setTo($toAddresses)
            ->setBody(
                $this->templating->render(
                    'email/workflow_send_email_action.html.twig',
                    ['body' => $body]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}