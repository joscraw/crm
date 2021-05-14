<?php

namespace App\Security;

use App\Entity\GmailAttachment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GmailAttachmentVoter extends Voter
{
    // these strings are just invented: you can use anything
    const DOWNLOAD_ATTACHMENT = 'download_attachment';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DOWNLOAD_ATTACHMENT])) {
            return false;
        }

        // only vote on Chat Message objects inside this voter
        if (!$subject instanceof GmailAttachment) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a ChatMessage object, thanks to supports
        /** @var GmailAttachment $gmailAttachment
         */
        $gmailAttachment = $subject;

        switch ($attribute) {
            case self::DOWNLOAD_ATTACHMENT:
                return $this->canDownloadAttachment($gmailAttachment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDownloadAttachment(GmailAttachment $gmailAttachment, User $user)
    {
        if($gmailAttachment->getPortal()->getId() === $user->getPortal()->getId()) {
            return true;
        }

        return false;
    }
}